<?php

require_once 'engine.php';

load_db();
$comment_exists = $db->querySingle(
  "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
);
if(! $comment_exists) {
  array_push($messages, array("Le commentaire pour le titre <b>$title</b> n'existe pas", "alert-danger"));
  $title = "";
} else {
  $raw_comment = $db->querySingle(
      "SELECT comment FROM notes WHERE title = '$title';"
  );
  if($raw_comment == "") {
    array_push($messages, array("Note vide"));
  }
}

md2html();
get_note_list();
get_document_list();

return_answer();

