<?php

require_once 'engine.php';

load_db();

$uploadFile = basename($_FILES['filename']['name']);
if(is_file($pool . "/" . $uploadFile)) {
  array_push($messages, array("Le fichier $uploadFile existe déjà", "alert-danger"));
  $title = "";
} else {
  if (move_uploaded_file($_FILES['filename']['tmp_name'], $pool . "/" . $uploadFile)) {
    $title = preg_replace("/\.\w+$/", "", $uploadFile);
    $raw_comment = "";
    $comment = "";

    $type = mime_content_type($pool . "/" . $uploadFile);
    $result = $db->exec(
        "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', ',$title,');"
    );
    if ($result) {
      array_push($messages, array("Fichier <b>$uploadFile</b> ajouté", "alert-success"));
      require_once 'create.php';
      // create.php already returns a JSON whole, so get out :
      return;
    } else {
      array_push($messages, array("Le fichier $uploadFile n'a pas pu être enregistré dans la DB", "alert-danger"));
      $title = "";
    }
  } else {
    array_push($messages, array("Le fichier <b>$uploadFile</b> n'a pas pu être copié dans <b>$pool</b>. Vérifiez les permissions sur le dossier", "alert-danger"));
    $title = "";
  }
}

return_answer();

