<?php

require_once 'engine.php';

load_db();

$uploadFile = basename($_FILES['filename']['name']);

if(is_file($pool . "/" . $uploadFile)) {
  array_push($messages, array("Le fichier $uploadFile existe déjà", "alert-danger"));
  $title = "";
} else {
  if (move_uploaded_file($_FILES['filename']['tmp_name'], $pool . "/" . $uploadFile)) {
    $type = mime_content_type($pool . "/" . $uploadFile);
    $result = $db->exec(
        "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', ',$title,');"
    );
    if ($result) { 
      array_push($messages, array("Fichier <b>$uploadFile</b> ajouté", "alert-success"));
    } else {
      array_push($messages, array("Le fichier <b>$uploadFile</b> n'a pas pu être ajouté à la base de données", "alert-danger"));
    }
  } else {
    array_push($messages, array("Le fichier <b>$uploadFile</b> n'a pas pu être copié dans <b>$pool</b>. Vérifiez les permissions sur le dossier", "alert-danger"));
  }
}

get_document_list();

return_answer();

