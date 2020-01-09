<?php

require_once 'engine.php';

load_db();

$note_exists = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title');"
);
if($note_exists) {
  try {
    $result = $db->exec("DELETE FROM notes WHERE title = '$title';");
    $message = "Note <b>$title</b> supprimée";
    $title = "";
  } catch(Throwable $err) {
    $message = $err.message;
    $messageType = "alert-danger";
  }
} else {
  $message = "La note $title n'existe pas";
  $messageType = "alert-warning";
}

require_once 'read.php';

