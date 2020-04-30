<?php

require_once 'engine.php';

load_db();

$documentQuery = $db->query("SELECT filename, attached_notes FROM documents");

while($doc = $documentQuery->fetchArray()) {
  $filename = $doc[0];
  var_dump($filename);
  $attachedNotes = $doc[1];
  $newAttachedNotes = "," . $attachedNotes;
  $db->querySingle(
    "UPDATE documents SET attached_notes = '" . $newAttachedNotes . "' WHERE filename = '$filename'"
  );
}

