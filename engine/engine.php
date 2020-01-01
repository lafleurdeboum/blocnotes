<?php

require_once 'Markdown/Markdown.inc.php';
use Michelf\Markdown;

$db = null;
$dbFile = "admin/notes.db";
$title = $_POST["title"] ?: "";
$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$noteList = array();
$pool = "documents";
$documents = array();
$message = null;
$messageType = null;

$returnObject = array();
$referer = parse_url($_SERVER['HTTP_REFERER']);


function load_db() {
  global $db, $dbFile, $message, $messageType;
  // Assume both db and engine commands live in ProgramRoot/ subdirectories :
  $workDir = preg_split("#/engine/#", getcwd())[0];

  if (! extension_loaded('sqlite3')) {
    $messageType = "alert-warning";
    $message = "Pas de support sqlite3 ! Pas d'Accès à la base de données <b>$dbFile</b>";
  } else {
    $db = new SQLite3("$workDir/$dbFile");
  }
}

function get_comment() {
  global $comment, $raw_comment, $message, $messageType;
  // Turn comment into html using markdown :
  if($raw_comment == "") {
    $comment = "";
    // Only return a message if the note is empty :
    $messageType = "alert-success";
    $message = "note vide : <b>$title</b>";
  } else {
    $comment = Markdown::defaultTransform($raw_comment);
    /*
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
     */
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
  $documentQuery = $db->query(
      "SELECT filename, filetype FROM documents WHERE instr(attached_notes, '$title');"
  );
  while ($document = $documentQuery->fetchArray()) {
    array_push($documents, array(
        'filename' => $document['filename'],
        'filetype' => $document['filetype']
    ));
  }
}

function return_answer() {
  global $title, $comment, $raw_comment, $documents, $message, $messageType, $noteList;

  $returnObject["title"] = $title;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["comment"] = $comment;
  $returnObject["documents"] = $documents;
  if ($message) {
    $returnObject["message"] = $message;
    $returnObject["messageType"] = $messageType;
  }
  if ($noteList) {
    $returnObject["notes"] = $noteList;
  }

  echo json_encode($returnObject);
  echo "\n";
}

