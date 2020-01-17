<?php

$createStatus = false;
load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title')"
);
if ($article_exists) {
    $createStatus = "La note <b>$title</b> existe déjà";
} else {
  try {
    $result = $db->exec(
      "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title', '" . SQLite3::escapeString($raw_comment) . "' )"
    );
    if ($result) {
      $createStatus = true;
    } else {
      $createStatus = "La nouvelle note n'a pas pu être enregistrée dans la base";
    }
  } catch (Throwable $error) {
    $createStatus = "La nouvelle note n'a pas pu être enregistrée dans la base";
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
}

require('note/read.php');
$status = $createStatus;

