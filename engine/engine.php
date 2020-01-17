<?php

require_once 'Markdown/Markdown.inc.php';
use Michelf\Markdown;

$db = null;
// Presume engine.php (this file) lives in "$programRoot/engine" :
$programRoot = preg_replace("%/engine$%", "", getcwd());
// Presume documents upload dir location :
$pool = $programRoot . "/documents";

$status = false;
// Let includer override $dbFile ;
if(array_key_exists('dbFile', $GLOBALS)) { $dbFile = $GLOBALS["dbFile"]; }
else { $dbFile = $programRoot . "/admin/notes.db"; }
// Let includer override $title and $raw_comment ;
if(array_key_exists('engine_call', $_POST)) { $engine_call = $_POST["engine_call"]; }
if(array_key_exists('title', $_POST)) { $title = $_POST["title"]; }
if(array_key_exists('raw_comment', $_POST)) { $raw_comment = $_POST["raw_comment"]; }
//else { $raw_comment = ""; }
$comment = "";
$noteList = array();
$documents = array();
// $messages is an array of arrays, each containing
// array(String message, [ String alertType="alert-info", [ Int timeout=4000 ]])
$messages = array();
$returnObject = array();

// Error / Warning / Exception catchall
// grabbed from https://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning#1241751
set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
  // If the error was escaped with the @ operator, let the process continue :
  if (0 === error_reporting()) {
    return false;
  }
  throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function messageUser($message, $alertType="alert-info", $timeout=4000) {
  global $messages;

  array_push($messages, array($message, $alertType, $timeout));
}

function load_db() {
  global $db, $dbFile, $messages;

  $dbFileRights = substr(sprintf('%o', fileperms("$dbFile")), -4);
  if($dbFileRights != "0666") {
    messageUser("La base de données n'est pas accessible en écriture", "alert-warning");
  }
  if(extension_loaded('sqlite3')) {
    try {
      $db = new SQLite3("$dbFile");
    } catch(Throwable $error) {
      messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
    }
  } else {
    messageUser("Pas de support sqlite3 ! <br />Pas d'Accès à la base de données <b>$dbFile</b>", "alert-warning");
  }
}

function md2html() {
  global $comment, $raw_comment, $messages;
  // Turn comment into html using markdown :
  if($raw_comment == "") {
    $comment = "";
  } else {
    $comment = Markdown::defaultTransform($raw_comment);
    // Separate comment into sections around h2 headers :
  
    $commentList = explode("<h2>", $comment);
    // Create a section on each header size 2 :
    if(substr_count($commentList[0], "</h2>")) {
      // Then comment began with "<h2>".
      $displayable = "<section><h2>";
    } else {
      $displayable = "<section>";
    }
    $displayable .= implode("</section><section><h2>", $commentList);
    $displayable .= "</section>";
    // Erase any empty section generated :
    $displayable = str_ireplace("<section><h2></section>", "", $displayable);
    $displayable = str_ireplace("<section></section>", "", $displayable);
    $comment = $displayable;
  }
}

function get_note_list() {
  global $db, $noteList, $messages;
  try {
    $notes = $db->query("SELECT title FROM notes;");
  } catch(Throwable $error) {
    messageUser($error->getMessage(), "alert-danger");
  }
  if ($notes) {
    while ($note = $notes->fetchArray()) {
      array_push($noteList, $note['title']);
    }
  }
}

function get_document_list() {
  global $db, $title, $documents;
  try {
    if($title == "") {
        $documentQuery = $db->query(
            "SELECT filename, filetype FROM documents WHERE attached_notes = ',';"
        );
    } else {
      $documentQuery = $db->query(
          "SELECT filename, filetype FROM documents WHERE instr(attached_notes, ',$title,');"
      );
    }
  } catch(Throwable $error) {
    if($error->getPrevious() == NULL) {
      // TODO Check that this assertion is meaningful here.
      // The query returned an empty list ; $documents is an empty array, we can
      return;
    }
  }
  while ($document = $documentQuery->fetchArray()) {
    array_push($documents, array(
        'filename' => $document['filename'],
        'filetype' => $document['filetype']
    ));
  }
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $post_max_size = parse_size(ini_get('post_max_size'));
    if ($post_max_size > 0) {
      $max_size = $post_max_size;
    }

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}
function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function return_answer() {
  global $status, $title, $comment, $raw_comment, $documents, $messages, $noteList;

  $returnObject["status"] = $status;
  $returnObject["title"] = $title;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["comment"] = $comment;
  // messages, notes and documents may be empty arrays.
  $returnObject["messages"] = $messages;
  $returnObject["notes"] = $noteList;
  $returnObject["documents"] = $documents;

  echo json_encode($returnObject);
  echo "\n";
}



if(is_file("$engine_call")) {
  try {
    require($engine_call);
  } catch (Throwable $error) {
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger", 0);
  }
} else {
  messageUser("'$engine_call' is not a valid engine call", "alert-danger", 0);
}

return_answer();

