<?php

$DBFile = "notes.db";
//$documentsFolder = "http://rarawoulib.org//keidee1epahmeiD9aiNoo4iluY/Rara/"
$comments = array();

// Uses $DBFile to initialize a DB in $db :
require_once("initialize.php");

echo "begin migration in DB file $DBFile";

// Set main comment from old db :
$mainComment = $db->querySingle(
  "SELECT comment FROM articles WHERE title = '';"
);
$retval = $db->querySingle(
  "UPDATE notes SET comment = '" . SQLite3::escapeString($mainComment) . "' WHERE title = ''"
);

$results = $db->query("SELECT title, comment FROM articles");
while($row = $results->fetchArray()) {
  array_push($comments, $row);
}

// Add a note with the title and comment (which probably contains links to documents)
foreach($comments as $entry) {
  $title = preg_replace("/\..*$/", "", $entry[0]);
  echo "processing $title \n";
  $db->exec(
    "INSERT OR REPLACE INTO notes(title, comment) VALUES ('$title', '" . SQLite3::escapeString($entry[1]) . "')"
  );
  //$ret = $db->querySingle("SELECT attached_notes FROM documents WHERE filename = '$entry[0]'");
  //echo "he : $ret\n";
}

// Populate documents table and attach all documents to main note (title='') :
$files = array_diff(scandir($documentsFolder), array('.', '..'));
foreach ($files as $file) {
  $type = mime_content_type($documentsFolder . $file);
  $db->exec(
    "INSERT OR IGNORE INTO documents (filename, filetype, attached_notes) VALUES ('$file', '$type', ',')"
  );
}

// Check for docs whose attached notes don't contain the mandatory ',' :
$unlinkedDocs = $db->query(
  "UPDATE documents SET attached_notes = ',' WHERE attached_notes = ''"
);

