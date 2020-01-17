<?php

load_db();
$comment_exists = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title')"
);
if(! $comment_exists) {
  $status = "Le commentaire pour le titre <b>$title</b> n'existe pas";
  $title = "";
} else {
  try {
    $raw_comment = $db->querySingle(
        "SELECT comment FROM notes WHERE title = '$title'"
    );
    md2html();
    $status = true;
  } catch (Throwable $error) {
    $status = "Impossible de lire la note $title";
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
}

get_note_list();
get_document_list();

