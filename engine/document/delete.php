<?php

$filename = $_POST["filename"] ?: "";
$fileDeleted = false;
$DBUpdated = false;

load_db();

try {
  $deleteFailed = $db->querySingle(
    "DELETE FROM documents WHERE filename = '$filename';"
  );
} catch (Throwable $error) {
  messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
}
if($deleteFailed != FALSE) {
  // The call succeeded.
  $DBUpdated = true;
}

if(is_file("$pool/$filename")) {
  if(unlink("$pool/$filename")) {
    $fileDeleted = true;
  }
} else {
  messageUser("Il n'y avait pas de fichier $filename dans $pool", "alert-warning");
}

if($DBUpdated) {
  if($fileDeleted) {
    messageUser("Fichier $filename supprimé et retiré de la base de données", "alert-success");
  } else {
    messageUser("Fichier $filename retiré de la base de données", "alert-success");
  }
} else if($fileDeleted) {
    messageUser("Fichier $filename supprimé", "alert-success");
} else {
    messageUser("Le fichier $filename n'a pas pu etre supprimé ni oublié", "alert-success");
}

get_document_list();
return_answer();

