<?php

load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if ($article_exists) {
    messageUser("La note <b>$title</b> existe déjà", "alert-danger");
} else {
  try {
    $result = $db->exec(
      "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title', '" . SQLite3::escapeString($raw_comment) . "' )"
    );
    if ($result) {
      messageUser("Nouvelle note enregistrée", "alert-success");
    } else {
      messageUser("La nouvelle note n'a pas été enregistrée", "alert-danger");
    }
  } catch (Throwable $error) {
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
}

require('note/read.php');

