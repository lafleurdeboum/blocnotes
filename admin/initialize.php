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

    echo 'got sqlite3 !<br />';

    $dbFile = "admin/articles.db";
    # create a new db. You might have to create the empty file $dbFile
    $db = new SQLite3($dbFile);
    $db->exec(
      "CREATE TABLE IF NOT EXISTS articles (
        title STRING,
        comment STRING,
        UNIQUE(title) )"
    );

    echo 'created new db<br />';
    # insert a comment holder for the main page
    $db->exec(
      "INSERT OR IGNORE INTO  articles(title, comment) VALUES (
        '__main__',
        '' )"
    );
    echo 'added default entry<br />';

    ## insert comment holders for each and every file - not necessary anymore ;
    ## post_comment.php creates the entry if necessary

    #$files = array_diff(scandir('Rara'), array('.', '..'));
    #foreach ($files as $file) {
    #  $db->exec(
    #    "INSERT OR IGNORE INTO files (filename, comment) VALUES (
    #      '" . $file . "',
    #      '' )"
    #  );
    #};

    # check contents
    $results = $db->query("SELECT title FROM articles");
    $i = 0;
    while ($row = $results->fetchArray()) {
      $i += 1;
      echo "la base {$dbFile} contient l'entrée : {$row['title']} <br />";
    };
    if ($i == 0) {
      echo "could not read DB !";
    };

  };
?>
  
</body>
</html>

