<?php
  if (extension_loaded('sqlite3')) {

    echo 'got sqlite3 !<br />';

    # create a new db. You might have to create the empty file rara.db
    $db = new SQLite3("rara.db");
    $db->exec(
      "CREATE TABLE IF NOT EXISTS files (
        filename STRING,
        comment STRING,
        UNIQUE(filename) )"
    );

    # insert a comment holder for the main page
    $db->exec(
      "INSERT OR IGNORE INTO files (filename, comment) VALUES (
        '__main__',
        '' )"
    );

    # insert comment holders for each and every file
    $files = array_diff(scandir('Rara'), array('.', '..'));
    foreach ($files as $file) {
      $db->exec(
        "INSERT OR IGNORE INTO files (filename, comment) VALUES (
          '" . $file . "',
          '' )"
      );
    };

    # check contents
    $results = $db->query("SELECT filename FROM files");
    while ($row = $results->fetchArray()) {
      echo 'inserted ' . $row['filename'] . '<br />';
    };

  };

