<?php

$dbFile = "admin/notes.db";
$action = $_POST["act"];
$title = $_POST["title"] ?: "";
$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$noteList = null;
$message = null;
$messageType = null;

$returnObject = array();
$referer = parse_url($_SERVER['HTTP_REFERER']);

function readNote() {
  global $db, $title, $comment, $raw_comment, $message, $messageType;
  require 'engine/readNote.php';
}
function postNote() {
  global $db, $title, $comment, $raw_comment, $message, $messageType;
  require 'engine/postNote.php';
}
function deleteNote() {
  global $db, $title, $message, $messageType;
  require 'engine/deleteNote.php';
}

if (! extension_loaded('sqlite3')) {
  $messageType = "alert-warning";
  $message = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
} else {

  $db = new SQLite3($dbFile);

  switch($action) {
    case "readNote":
      readNote();
      break;
    case "postNote":
      postNote();
      break;
    case "deleteNote":
      deleteNote();
      break;
    case "debug":
      $messageType = "alert-warning";
      $message = "I'm in";
      $comment = "here too";
  }

  // List notes in db :
  $noteList = array();
  $notes = $db->query("SELECT title FROM notes;");
  if ($notes) {
    while ($note = $notes->fetchArray()) {
      array_push($noteList, $note['title']);
    }
  }
}

$returnObject["title"] = $title;
$returnObject["raw_comment"] = $raw_comment;
$returnObject["comment"] = $comment;
if ($message) {
  $returnObject["message"] = $message;
  $returnObject["messageType"] = $messageType;
}
if ($noteList) {
  $returnObject["notes"] = $noteList;
}

echo json_encode($returnObject);
echo "\n";

