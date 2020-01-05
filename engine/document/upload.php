<?php

require_once 'engine.php';

load_db();

//$uploadFile = basename($_FILES['userfile']['name']);
$uploadFile = basename($_FILES['file']['name']);

//if (move_uploaded_file($_FILES['userfile']['tmp_name'], $pool . $uploadFile)) {
if (move_uploaded_file($_FILES['file']['tmp_name'], $pool . "/" . $uploadFile)) {
  $messageType = "alert-success";
  $type = mime_content_type($pool . "/" . $uploadFile);
  $message = "Fichier <b>$uploadFile</b> ajouté à $pool, type $type, attaché au titre '$title'.";
  $result = $db->exec(
      "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', '$title');"
  );
  if (!$result) { $message .= " - non enregistré dans la DB"; }
} else {
  $messageType = "alert-warning";
  $message = "Le fichier <b>$uploadFile</b> n'a pas pu être ajouté.
      Vérifiez les permissions sur le dossier.";
}

get_document_list();

return_answer();

