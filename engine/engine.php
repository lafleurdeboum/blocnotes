<?php

require_once 'Markdown/Markdown.inc.php';
use Michelf\Markdown;

$db = null;
$dbFile = "admin/rara.db";
// Presume both db and engine commands live in "$ProgramRoot/engine" :
$workDir = preg_split("#/engine/#", getcwd())[0];
// Presume documents upload dir location :
$pool = "$workDir/documents";

// Let includer override $title ;
if(! array_key_exists('title', $GLOBALS)) { $title = $_POST["title"]; }

$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$noteList = array();
$documents = array();
$message = null;
$messageType = null;
$returnObject = array();


function load_db() {
  global $db, $dbFile, $workDir, $message, $messageType;

  if (! extension_loaded('sqlite3')) {
    $messageType = "alert-warning";
    $message = "Pas de support sqlite3 ! <br />Pas d'Accès à la base de données <b>$dbFile</b>";
  } else {
    $db = new SQLite3("$workDir/$dbFile");
  }
}

function md2html() {
  global $comment, $raw_comment, $message, $messageType;
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
  global $db, $noteList;
  $notes = $db->query("SELECT title FROM notes;");
  if ($notes) {
    while ($note = $notes->fetchArray()) {
      array_push($noteList, $note['title']);
    }
  }
}

function get_document_list() {
  global $db, $title, $documents;
  if($title == "") {
    $documentQuery = $db->query(
        "SELECT filename, filetype FROM documents WHERE attached_notes = '';"
    );
  } else {
    $documentQuery = $db->query(
        "SELECT filename, filetype FROM documents WHERE instr(attached_notes, '$title');"
    );
  }
  while ($document = $documentQuery->fetchArray()) {
    array_push($documents, array(
        'filename' => $document['filename'],
        'filetype' => $document['filetype']
    ));
  }
}

function return_answer() {
  global $title, $comment, $raw_comment, $documents, $message, $messageType, $noteList;

  // Always return a title and comment ; optionaly return a message and a list of nodes and documents.

  $returnObject["title"] = $title;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["comment"] = $comment;
  $returnObject["message"] = $message;
  $returnObject["messageType"] = $messageType;
  $returnObject["notes"] = $noteList;
  $returnObject["documents"] = $documents;

  echo json_encode($returnObject);
  echo "\n";
}

