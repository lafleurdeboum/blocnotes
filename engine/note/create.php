<?php

require_once "engine.php";

load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if ($article_exists) {
    $messageType = "alert-danger";
    $message = "La note $title existe déjà";
} else {
  $result = $db->exec(
    "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title',
      '" . SQLite3::escapeString($raw_comment) . "' );"
  );
  if (! $result) {
    $messageType = "alert-danger";
    $message = "Le nouveau commentaire n'a pas été enregistré";
  } else {
    $messageType = "alert-success";
    $message = "Nouveau commentaire enregistré";
  }
}

require_once 'read.php';

