<?php

require_once 'engine.php';

global $db, $title, $raw_comment, $comment, $message, $messageType;

load_db();

$result = $db->exec("DELETE FROM notes WHERE title = '$title';");
if ($result) {
  $messageType = "alert-success";
  $message = "Note <b>$title</b> supprimée";
  $title = "";
} else {
  $messageType = "alert-warning";
  $message = "La note <b>$title</b> n'a pas pu être supprimée";
}

require_once 'readNote.php';

