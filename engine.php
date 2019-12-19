<?php

$dbFile = "admin/notes.db";
$action = $_POST["act"];
$title = $_POST["title"] ?: "";
$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$articleList = null;
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

  // List articles in db :
  $articleList = array();
  $articles = $db->query("SELECT title FROM articles;");
  if ($articles) {
    while ($article = $articles->fetchArray()) {
      array_push($articleList, $article['title']);
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
if ($articleList) {
  $returnObject["articles"] = $articleList;
}

echo json_encode($returnObject);
echo "\n";

