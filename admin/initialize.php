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

    if(! array_key_exists("DBFile", $GLOBALS)) { $DBFile = "notes.db"; }
    if(! array_key_exists("documentsFolder", $GLOBALS)) { $documentsFolder  = "../documents/"; }

    # Create a new db. You might have to create the empty file $DBFile
    $db = new SQLite3($DBFile);
    $db->exec(
      "CREATE TABLE IF NOT EXISTS notes (
        title STRING,
        comment STRING,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(title) )"
    );
    $db->exec(
      "CREATE TABLE IF NOT EXISTS documents (
        filename STRING,
        filetype STRING,
        attached_notes STRING,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(filename) )"
    );
    $db->exec(
      "CREATE TRIGGER [updateTimestamp] AFTER UPDATE ON notes FOR EACH ROW BEGIN
       UPDATE notes SET timestamp = CURRENT_TIMESTAMP WHERE title = old.title;
       END"
    );

    echo "created new db $DBFile<br />\n";
    # Insert a comment holder for the main page
    $db->exec(
      "INSERT OR IGNORE INTO  notes(title, comment) VALUES (
        '',
        '' )"
    );
    echo "added default entry<br />\n";

    # Check contents :
    $i = 0;
    $results = $db->query("SELECT title FROM notes");
    while ($row = $results->fetchArray()) {
      $i += 1;
      echo "la base {$DBFile} contient l'entrée : {$row['title']} <br />\n";
    };
    $results = $db->query("SELECT filename FROM documents");
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

