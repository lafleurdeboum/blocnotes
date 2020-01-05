<?php

require_once 'engine.php';

$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  $message = "Il n'y a pas de fichier $filename dans $pool";
  $messageType = "alert-warning";
  get_document_list();
  return_answer();
  return;
}

load_db();

$deleteFailed = $db->querySingle(
  "DELETE FROM documents WHERE filename = '$filename';"
);
if($deleteFailed) {
  $message = "Le fichier $filename n'a pas pu être oublié";
  $messageType = "alert-warning";
  get_document_list();
  return_answer();
  return;
}
if(unlink("$pool/$filename")) {
  $message = "Le fichier $filename a été supprimé et oublié";
} else {
  $message = "Le fichier $filename est oublié, mais existe encore dans $pool";
}

$title = "";
get_document_list();
return_answer();

