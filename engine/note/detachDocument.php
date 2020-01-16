<?php

$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  messageUser("Il n'y a pas de fichier $filename dans $pool", "alert-warning");
} else {

  load_db();

  $attachedNotes = $db->querySingle(
      "SELECT attached_notes FROM documents WHERE filename = '$filename';"
  );
  if(strpos($attachedNotes, $title) === false) {
    messageUser("Le fichier $filename n'était pas lié à la note $title", "alert-warning");
  } else {
    $newAttachedNotes = preg_replace("/$title,/", "", $attachedNotes);

    $tagged = $db->querySingle(
      "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename';"
    );
    messageUser("Le fichier $filename est délié de la note $title");
    messageUser("Le fichier $filename est maintenant lié à $newAttachedNotes");
  }

  // DEBUG Do note return a whole note, the view should stay the same :
  return_answer();
}

