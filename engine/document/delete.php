<?php

require_once 'engine.php';

$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  $message = "Name $filename is not a valid file under $pool";
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
  $message = "Deleting $filename from DB failed !";
  $messageType = "alert-warning";
  get_document_list();
  return_answer();
  return;
}
if(unlink("$pool/$filename")) {
  $message = "Document $filename was deleted";
} else {
  $message = "Deleting $filename from DB succeeded - file still lives at $pool/$filename though";
}

get_document_list();
return_answer();

