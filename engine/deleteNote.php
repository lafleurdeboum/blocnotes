<?php

$result = $db->exec("DELETE FROM notes WHERE title = '$title';");

if ($result) {
  $messageType = "alert-success";
  $message = "Note <b>$title</b> supprimée";

} else {
  $messageType = "alert-warning";
  $message = "La note <b>$title</b> n'a pas pu être supprimée";
}

