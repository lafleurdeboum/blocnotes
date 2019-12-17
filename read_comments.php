<?php
  $file = $_GET['file'];
  if (extension_loaded('sqlite3')) { } else { echo 'no sqlite 3 !<br />'; }
  $db = new SQLite3("rara.db");
  $statement = $db->prepare("SELECT * FROM files WHERE filename = :filename;");
  $statement->bindValue(':filename', $file);
  $result = $statement->execute();
  $val = $result->fetchArray();
  echo $val['comment'];

