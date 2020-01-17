<?php

$detachStatus = false;
$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  $detachStatus = "Il n'y a pas de fichier $filename dans $pool";
} else {

  load_db();

  $attachedNotes = $db->querySingle(
      "SELECT attached_notes FROM documents WHERE filename = '$filename'"
  );
  if(strpos($attachedNotes, $title) === false) {
    $detachStatus = "Le fichier $filename n'était pas lié à la note $title";
  } else {
    $newAttachedNotes = preg_replace("/$title,/", "", $attachedNotes);

    try {
      $tagged = $db->querySingle(
        "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename'"
      );
      $detachStatus = true;
      messageUser("Le fichier $filename est maintenant lié à $newAttachedNotes");
    } catch (Throwable $error) {
      $detachStatus = "Impossible de détacher $filename";
      messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
    }
  }
  // DEBUG Do note return a whole note, the view should stay the same :
}

$status = $detachStatus;

