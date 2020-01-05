<?php

require_once 'engine.php';

load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if ($article_exists) {
  $writeFailed = $db->querySingle(
      "UPDATE notes SET comment = '" . SQLite3::escapeString($raw_comment) . "' WHERE title = '$title';"
  );
  if ($writeFailed) {
    // We never reach here ; we'd better try testing if the db file is rw enabled.
    $messageType = "alert-warning";
    $message = "Le commentaire n'a pas pu être mis à jour";
  } else {
    $messageType = "alert-success";
    $message = "Commentaire mis à jour";
  }
} else {
  $messageType = "alert-warning";
  $message = "Note $title non trouvée";
}

require_once 'read.php';

