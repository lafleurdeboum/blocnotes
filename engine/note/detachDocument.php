<?php

require_once 'engine.php';

$filename = $_POST["filename"] ?: "";

if(! is_file("$pool/$filename")) {
  $message = "Name $filename is not a valid file under $pool";
  $messageType = "alert-warning";
  return_answer();
  return;
}

load_db();

//"SELECT attached_notes FROM documents WHERE instr(attached_notes, '$title');"
$attachedNotes = $db->querySingle(
    "SELECT attached_notes FROM documents WHERE filename = '$filename';"
);
if(strpos($attachedNotes, $title) === false) {
  $message = "File $filename was not attached to note $title anyway";
  $messageType = "alert-warning";
  return_answer();
  return;
}

$newAttachedNotes = preg_replace("/$title,/", "", $attachedNotes);

$tagged = $db->querySingle(
  "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename';"
);
$message = "$filename detached from $title - now attached to $newAttachedNotes";
return_answer();

