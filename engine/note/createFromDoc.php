<?php

require_once 'engine.php';

load_db();

//$uploadFile = basename($_FILES['userfile']['name']);
$uploadFile = basename($_FILES['filename']['name']);
if(is_file($pool . "/" . $uploadFile)) {
  $message = "Le fichier $uploadFile existe déjà";
  $messageType = "alert-danger";
  $title = "";
  return_answer();
  return;
}

//if (move_uploaded_file($_FILES['userfile']['tmp_name'], $pool . $uploadFile)) {
if (move_uploaded_file($_FILES['filename']['tmp_name'], $pool . "/" . $uploadFile)) {
  $title = preg_replace("/\.\w+$/", "", $uploadFile);
  $raw_comment = "";
  $comment = "";

  $type = mime_content_type($pool . "/" . $uploadFile);
  $result = $db->exec(
      "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', ',$title,');"
  );
  $message = "Fichier <b>$uploadFile</b> ajouté, type $type, attaché au titre '$title'.";
  if ($result) {
    require_once 'create.php';
  } else {
    $message .= " - non enregistré dans la DB";
    return_answer();
  }
} else {
  $messageType = "alert-warning";
  $message = "Le fichier <b>$uploadFile</b> n'a pas pu être ajouté.
      Vérifiez les permissions sur le dossier.";
  return_answer();
}

