<!DOCTYPE html>
<html>
<head>
  <!-- refuse indexing -->
  <meta name="robots" content="none" />
  <!-- Required meta tags -->
  <meta charset="utf-8">
</head>
<body>

<?php
  if (extension_loaded('sqlite3')) {

    echo "got sqlite3 !<br />\n";

    $DBFile = "admin/notes.db";
    $documentsFolder = "documents";
    # create a new db. You might have to create the empty file $DBFile
    $db = new SQLite3($DBFile);
    $db->exec(
      "CREATE TABLE IF NOT EXISTS notes (
        title STRING,
        comment STRING,
        UNIQUE(title) )"
    );
    $db->exec(
      "CREATE TABLE IF NOT EXISTS documents (
        filename STRING,
        filetype STRING,
        attached_notes STRING,
        UNIQUE(filename) )"
    );

    echo "created new db<br />\n";
    # insert a comment holder for the main page
    $db->exec(
      "INSERT OR IGNORE INTO  notes(title, comment) VALUES (
        '',
        '' )"
    );
    echo "added default entry<br />\n";

    ## populate files table :

    $files = array_diff(scandir($documentsFolder), array('.', '..'));
    foreach ($files as $file) {
      $type = mime_content_type("documents/" . $file);
      $db->exec(
        "INSERT OR IGNORE INTO documents (filename, filetype, attached_notes) VALUES ('$file', '$type', '')"
      );
    };

    # check contents
    $i = 0;
    $results = $db->query("SELECT title FROM notes");
    while ($row = $results->fetchArray()) {
      $i += 1;
      echo "la base {$DBFile} contient l'entrée : {$row['title']} <br />\n";
    };
    $results = $db->query("SELECT filename FROM documents");
    $i = 0;
    while ($row = $results->fetchArray()) {
      $i += 1;
      echo "la base {$DBFile} contient l'entrée : {$row['filename']} <br />\n";
    };
    if ($i == 0) {
      echo "could not read DB !<br />\n";
    };

  };
?>
  
</body>
</html>

