<?php

if(array_key_exists("old_title", $_POST)) {
  $old_title = $_POST["old_title"];
} else {
  $old_title = $title;
}

load_db();

$note_exists = $db->querySingle(
  "SELECT EXISTS(SELECT title FROM notes WHERE title = '$old_title');"
);
if($note_exists) {
  // Update comment :
  try {
    $db->querySingle(
        "UPDATE notes SET comment = '" . SQLite3::escapeString($raw_comment) . "' WHERE title = '$old_title';"
    );
    messageUser("Note mise à jour", "alert-success");
  } catch(Throwable $error) {
    messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
  }
  if($old_title != $title) {
    $new_note_exists = $db->querySingle(
      "SELECT EXISTS(SELECT title FROM notes WHERE title = '$title');"
    );
    if(! $new_note_exists) {
      try {
        $db->querySingle(
            "UPDATE notes SET title = '$title' WHERE title = '$old_title';"
        );
        $relinkableDocs = $db->query(
            "SELECT filename, attached_notes FROM documents WHERE instr(attached_notes, '$old_title,')"
        );
        while($row = $relinkableDocs->fetchArray()) {
          $filename = $row[0];
          $attachedNotes = $row[1];
          $newAttachedNotes = preg_replace("/$old_title,/", "$title,", $attachedNotes);
          $db->querySingle(
              "UPDATE documents SET attached_notes = '$newAttachedNotes' WHERE filename = '$filename'"
          );
        }
        messageUser("Note renommée", "alert-success");
      } catch(Throwable $error) {
        messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
      }
    } else {
      messageUser("La note <b>$title</b> existe déjà", "alert-danger");
      $title = "";
    }
  }
} else {
  messageUser("La note <b>$old_title</b> n'existe pas", "alert-danger");
  $title = "";
}

require('note/read.php');

