<?php

$filename = $_POST["filename"];
$fileDeleted = false;
$DBUpdated = false;
$deleteStatus = false;

load_db();

$doc_in_db = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title');"
);
if($doc_in_db) {
  try {
    $db->exec(
      "DELETE FROM documents WHERE filename = '$filename';"
    );
    $DBUpdated = true;
  } catch (Throwable $error) {
    $deleteStatus = "Impossible de supprimer $filename de la base de données";
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }

  if($DBUpdated) {
    if(is_file("$pool/$filename")) {
      if(unlink("$pool/$filename")) {
        $deleteStatus = true;
        if(is_file("$pool/thumbnails/$filename")) {
          try {
            unlink("$pool/thumbnails/$filename");
          } catch (Throwable $error) {  }
        }
      } else {
        $deleteStatus = "Impossible de supprimer le fichier $filename";
      }
    } else {
      $deleteStatus = "Il n'y avait pas de fichier $filename dans $pool";
      // So having it deleted from DB is all right.
    }
  }
} else {
  $deleteStatus = "Le fichier $filename n'était pas répertorié";
}

if($deleteStatus == true) {
  get_document_list();
}

$status = $deleteStatus;

