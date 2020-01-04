<?php

$DBFile = "rara.db";
//$documentsFolder = "http://rarawoulib.org//keidee1epahmeiD9aiNoo4iluY/Rara/"
$comments = array();

require_once("initialize.php");

echo "begin migration from db $DBFile";

// Set main comment from old db :
$mainComment = $db->querySingle(
  "SELECT comment FROM files WHERE filename = '__main__';"
);
$retval = $db->querySingle(
  "UPDATE notes SET comment = '" . SQLite3::escapeString($mainComment) . "' WHERE title = '';"
);

$results = $db->query("SELECT filename, comment FROM files;");
while($row = $results->fetchArray()) {
  array_push($comments, $row);
}

// Add a note with title $filename and the comment, and link document to it.
foreach($comments as $entry) {
  $title = preg_replace("/\..*$/", "", $entry[0]);
  echo "processing $title \n";
  $db->exec(
    "INSERT OR REPLACE INTO notes(title, comment) VALUES ('$title', '" . SQLite3::escapeString($entry[1]) . "');"
  );
  $db->exec(
    //"REPLACE INTO documents(filename, attached_notes) VALUES ('$entry[0]', attached_notes || '$entry[0],');"
    "UPDATE documents SET attached_notes = attached_notes || '$title' || ',' WHERE filename = '$entry[0]';"
  );
  //$ret = $db->querySingle("SELECT attached_notes FROM documents WHERE filename = '$entry[0]'");
  //echo "he : $ret\n";
  
}

$unlinkedDocs = $db->query(
  "SELECT filename FROM documents WHERE attached_notes = ''"
);
while($row = $unlinkedDocs->fetchArray()) {
  $title = preg_replace("/\..*$/", "", $row[0]);
  $db->exec(
    "INSERT OR REPLACE INTO notes(title, comment) VALUES ('$title', '');"
  );
  $db->exec(
    //"REPLACE INTO documents(filename, attached_notes) VALUES ('$entry[0]', attached_notes || '$entry[0],');"
    "UPDATE documents SET attached_notes = attached_notes || '$title' || ',' WHERE filename = '$row[0]';"
  );
}

