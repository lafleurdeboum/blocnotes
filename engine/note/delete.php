<?php

require_once 'engine.php';

load_db();

$result = $db->exec("DELETE FROM notes WHERE title = '$title';");
if ($result) {
  $messageType = "alert-success";
  $message = "Note <b>$title</b> supprimée";
  $title = "";
} else {
  $messageType = "alert-danger";
  $message = "La note <b>$title</b> n'a pas pu être supprimée";
}

require_once 'read.php';

