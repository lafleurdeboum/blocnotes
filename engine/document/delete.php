<?php

require_once 'engine.php';

$filename = $_POST["filename"] ?: "";
$fileDeleted = false;
$DBUpdated = false;

load_db();

// DELETE queries return true whatsoever.
$deleteFailed = $db->querySingle(
  "DELETE FROM documents WHERE filename = '$filename';"
);
if($deleteFailed != FALSE) {
  // The call succeeded.
  $DBUpdated = true;
}

if(is_file("$pool/$filename")) {
  if(unlink("$pool/$filename")) {
    $fileDeleted = true;
  }
} else {
  $message = "Il n'y avait pas de fichier $filename dans $pool";
  $messageType = "alert-warning";
}

if($DBUpdated) {
  if($fileDeleted) {
    $message .= " le fichier $filename a été supprimé et oublié";
  } else {
  $message .= " le fichier $filename a été oublié";  
  }
} else if($fileDeleted) {
  $message .= " le fichier $filename a été supprimé";
} else {
  $message .= " le fichier $filename n'a pas pu etre supprimé ni oublié";
  $messageType = "alert-warning";
}


$title = "";
get_document_list();
return_answer();

