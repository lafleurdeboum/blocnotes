<?php

load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if ($article_exists) {
    array_push($messages, array("La note <b>$title</b> existe déjà", "alert-danger"));
} else {
  try {
    $result = $db->exec(
      "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title', '" . SQLite3::escapeString($raw_comment) . "' )"
    );
    if ($result) {
      array_push($messages, array("Nouvelle note enregistrée", "alert-success"));
    } else {
      array_push($messages, array("La nouvelle note n'a pas été enregistrée", "alert-danger"));
    }
  } catch (Throwable $error) {
    array_push($messages, array($error->getMessage(), "alert-danger"));
  }
}

require('note/read.php');

