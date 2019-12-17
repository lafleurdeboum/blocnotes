<!DOCTYPE html>
<html>
<head>
  <!-- refuse indexing -->
  <meta name="robots" content="none" />
  <meta charset="utf-8">

  <?php

    $dbFile = "admin/articles.db";
    $returnQuery = array();
    $returnURL = parse_url($_SERVER['HTTP_REFERER']);

    //parse_str($returnURL["query"], $returnQuery);

    if ( ! extension_loaded('sqlite3')) {
      $returnQuery["alertType"] = "alert-warning";
      $returnQuery["alert"] = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
    } else {
      $title = $_POST['title'];
      $db = new SQLite3($dbFile);

      $article_exists = $db->querySingle(
          "SELECT EXISTS(SELECT comment FROM articles WHERE title = '$title');"
      );

      if ($article_exists) {
        echo "Deleting article $title";
        $result = $db->exec("DELETE FROM articles WHERE title = '$title';");
        if (! $result) {
          $returnQuery["alertType"] = "alert-warning";
          $returnQuery["alert"] = "Il y a eu un problème.
              La note <b>$title</b> n'a pas été supprimée.";
        } else {
          $returnQuery["alertType"] = "alert-success";
          $returnQuery["alert"] = "La note <b>$title</b> a bien été supprimée.";
        }
      } else {
        $returnQuery["alertType"] = "alert-warning";
        $returnQuery["alert"] = "La note $title ne peut pas être supprimée :
           elle est introuvable.";
      }
    }

    $end_url = $returnURL["scheme"] . "://" . $returnURL["host"];
    $end_url .= $returnURL["path"] . "?" . http_build_query($returnQuery);
?>

  <!-- Callback to redirect current page to caller page : -->
  <meta http-equiv="refresh" content="0; url=<?php echo $end_url ?>" />
</head>
<body id="content">

  <!-- Display committed changes in case the caller is unavailable : -->
  <p>
  <?php
    
    if ($article_exists) {
        echo "Note <b>$title</b> deleted.";
    } else {
        echo "Note <b>$title</b> not found";
    }

  ?>
  </p>
  <br />
  <a href="<?php echo $end_url; ?>">revenir</a>

</body>
</html>
