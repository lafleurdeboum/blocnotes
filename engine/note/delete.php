<?php

require_once 'engine.php';

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
    $message = "Note <b>$title</b> supprimée";
    $title = "";
  } catch(Throwable $err) {
    $message = $err.message;
    $messageType = "alert-danger";
  }
} else {
  $message = "La note $title n'existe pas";
  $messageType = "alert-warning";
}

require_once 'read.php';

