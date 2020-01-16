<?php

load_db();
$comment_exists = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title');"
);
if(! $comment_exists) {
  messageUser("Le commentaire pour le titre <b>$title</b> n'existe pas", "alert-danger");
  $title = "";
} else {
  try {
    $raw_comment = $db->querySingle(
        "SELECT comment FROM notes WHERE title = '$title';"
    );
    md2html();
  } catch (Throwable $error) {
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
}

get_note_list();
get_document_list();

return_answer();

