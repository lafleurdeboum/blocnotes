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
    $messageType = "alert-warning";
    $message = "Il y a eu un problème.
        Le commentaire n'a pas été mis à jour pour la note <b>$title</b>";
  } else {
    $messageType = "alert-success";
    $message = "Commentaire mis à jour pour la note <b>$title</b>";
  }
} else {
  $messageType = "alert-warning";
  $message = "note $title non trouvée.";
}

require_once 'read.php';

