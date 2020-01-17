<?php

$deleteStatus = false;
load_db();

$note_exists = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title');"
);
if($note_exists) {
  try {
    $db->exec("DELETE FROM notes WHERE title = '$title';");
    $linkedFiles = $db->query(
      "SELECT filename, attached_notes FROM documents WHERE instr(attached_notes, '$title');"
    );
    while ($row = $linkedFiles->fetchArray()) {
      $filename = $row[0];
      $attachedNotes = $row[1];
      $newAttachedNotes = preg_replace("/$title,/", "", $attachedNotes);
      $db->querySingle(
        "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename';"
      );
    }
    $deleteStatus = true;
    $title = "";
  } catch(Throwable $error) {
    $deleteStatus = "Impossible de supprimer la note $title";
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
} else {
  $deleteStatus = "La note $title n'existait pas";
  $title = "";
}

require_once 'note/read.php';
$status = $deleteStatus;

