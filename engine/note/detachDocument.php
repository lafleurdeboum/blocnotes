<?php

require_once 'engine.php';

$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  array_push($messages, array("Il n'y a pas de fichier $filename dans $pool", "alert-warning"));
  return_answer();
  return;
}

load_db();

//"SELECT attached_notes FROM documents WHERE instr(attached_notes, '$title');"
$attachedNotes = $db->querySingle(
    "SELECT attached_notes FROM documents WHERE filename = '$filename';"
);
if(strpos($attachedNotes, $title) === false) {
  array_push($messages, array("Le fichier $filename n'était pas lié à la note $title", "alert-warning"));
  return_answer();
  return;
}

$newAttachedNotes = preg_replace("/$title,/", "", $attachedNotes);

$tagged = $db->querySingle(
  "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename';"
);
array_push($messages, array("Le fichier $filename est délié de la note $title <br />- maintenant lié à $newAttachedNotes"));

// Do note return a whole note, the view should stay the same :
return_answer();

