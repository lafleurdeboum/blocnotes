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

    ## insert comment holders for each and every file - not necessary anymore ;
    ## post_comment.php creates the entry if necessary

    $files = array_diff(scandir($documentsFolder), array('.', '..'));
    foreach ($files as $file) {
      $db->exec(
        "INSERT OR IGNORE INTO documents (filename, attached_notes) VALUES ('$file', '')"
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

