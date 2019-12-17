<!DOCTYPE html>
<html>
<body>

<?php
  $file = $_POST['file'];
  $comment = "<i>Commentaire posté le " . date("d-m-Y", time()) . " à " . date("H:i") . "</i><br />";
  $comment .= "<pre>" . utf8_encode($_POST['comment']) . "</pre>";
  echo "fichier :" . $file . "<br />";
  echo "commentaire : " . utf8_decode($comment) . "<br />";

  if (extension_loaded('sqlite3')) {
    $db = new SQLite3("rara.db");

    ## get the past value of the comments
    $statement = $db->prepare("SELECT * FROM files WHERE filename = :filename;");
    $statement->bindValue(':filename', $file);
    $result = $statement->execute();
    print_r($result);
    $val = $result->fetchArray();

    if ( $val == "" ) {
      ## no item in db
      echo "creating entry in db : ";
      $db->exec(
        "INSERT OR IGNORE INTO files (filename, comment) VALUES (
          '" . $file . "',
          '" . $comment . "' )"
      );
    } else {
      ## update comment value in db
      $old_comments = $val["comment"];

      #$result = $db->query("UPDATE files SET comment = " . $comment . " WHERE filename = " . $file . ";");
      #$val = $result.fetchArray();
      $statement = $db->prepare("UPDATE files SET comment = :comment WHERE filename = :filename;");
      $statement->bindValue(':filename', $file);
      $statement->bindValue(':comment', $old_comments . $comment);
      $result = $statement->execute();
    }
    echo utf8_decode("commentaire enregistré");
  } else {
    echo 'no sqlite3 !<br />Could not post comment to ' . $file;
  }
?>
</body>
</html>
