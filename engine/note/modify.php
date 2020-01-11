<?php
require_once 'engine.php';

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
    array_push($messages, array("Commentaire mis à jour"));
  } catch(Throwable $err) {
    array_push($messages, array($err.message, "alert-danger"));
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
        array_push($messages, array("Note déplacée ; document reliés", "alert-success"));
      } catch(Throwable $err) {
        array_push($messages, array($err.message, "alert-danger"));
      }
    } else {
      array_push($messages, array("La note <b>$title</b> existe déjà", "alert-danger"));
      $title = "";
    }
  }
} else {
  array_push($messages, array("La note <b>$old_title</b> n'existe pas", "alert-danger"));
  $title = "";
}

require_once 'read.php';

