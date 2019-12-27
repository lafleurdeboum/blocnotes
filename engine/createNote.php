<?php

require_once "engine.php";

global $db, $title, $raw_comment, $comment, $message, $messageType, $documents, $noteList;

load_db();

$article_exists = $db->querySingle(
    "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if ($article_exists) {
    $messageType = "alert-warning";
    $message = "la note $title existe déjà.";
} else {
  $result = $db->exec(
    "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title',
      '" . SQLite3::escapeString($raw_comment) . "' );"
  );
  if (! $result) {
    $messageType = "alert-warning";
    $message = "Le nouveau commentaire n'a pas été enregistré pour l'article
        <b>$title</b>.<br />";
  } else {
    $messageType = "alert-success";
    $message = "Nouveau commentaire enregistré pour l'article <b>$title</b>";
  }
}

get_comment();
get_note_list();
get_document_list();

return_answer();

