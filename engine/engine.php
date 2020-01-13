<?php

require_once 'Markdown/Markdown.inc.php';
use Michelf\Markdown;

$db = null;
// Let includer override $title and $dbFile ;
if(! array_key_exists('title', $GLOBALS)) { $title = $_POST["title"]; }
if(array_key_exists('dbFile', $GLOBALS)) { $dbFile = $GLOBALS["dbFile"]; }
else { $dbFile = "admin/notes.db"; }
//$dbFile = "admin/rara.db";
// Presume both db and engine commands live in "$ProgramRoot/engine" :
$workDir = preg_split("#/engine/#", getcwd())[0];
// Presume documents upload dir location :
$pool = "$workDir/documents";

$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$noteList = array();
$documents = array();
$messages = array();
$returnObject = array();

// Let php warnings become errors so as to catch sqlite errors :
/*
set_error_handler(function($errno, $errstr, $errfile, $errline){
  if($errno === E_WARNING){
    trigger_error($errstr, E_ERROR);
    return true;
  } else {
    // Fallback to default php error handler :
    return false;
  }
});
 */

function load_db() {
  global $db, $dbFile, $workDir, $messages;

  $dbFileRights = substr(sprintf('%o', fileperms("$workDir/$dbFile")), -4);
  if($dbFileRights != "0666") {
    array_push($messages, array("La base de données n'est pas accessible en écriture", "alert-warning"));
  }
  if(extension_loaded('sqlite3')) {
    try {
      $db = new SQLite3("$workDir/$dbFile");
    } catch(Throwable $err) {
      array_push($messages, array($err.message, "alert-danger"));
    }
  } else {
    array_push($messages, array("Pas de support sqlite3 ! <br />Pas d'Accès à la base de données <b>$dbFile</b>", "alert-warning"));
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
  } catch(Throwable $err) {
    array_push($messages, array($err.message, "alert-danger"));
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
  } catch(Throwable $err) {
    if($err->getPrevious() == NULL) {
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

function return_answer() {
  global $title, $comment, $raw_comment, $documents, $messages, $noteList;

  // messages, notes and documents may be empty arrays.
  $returnObject["title"] = $title;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["comment"] = $comment;
  $returnObject["messages"] = $messages;
  $returnObject["notes"] = $noteList;
  $returnObject["documents"] = $documents;

  echo json_encode($returnObject);
  echo "\n";
}

