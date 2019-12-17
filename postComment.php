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

    parse_str($returnURL["query"], $returnQuery);

    if ( ! extension_loaded('sqlite3')) {
      $returnQuery["alertType"] = "alert-warning";
      $returnQuery["alert"] = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
    } else {
      $title = $_POST['title'];
      $comment = $_POST['comment'];
      $db = new SQLite3($dbFile);

      $article_exists = $db->querySingle(
          "SELECT EXISTS(SELECT comment FROM articles WHERE title = '" . $title . "');"
      );

      if (! $article_exists) {
        echo "Comment for new article $title :";
        $result = $db->exec(
          "INSERT OR IGNORE INTO articles (title, comment) VALUES ('$title',
            '" . SQLite3::escapeString($comment) . "' );"
        );
        if (! $result) {
          $returnQuery["alertType"] = "alert-warning";
          $returnQuery["alert"] = "Il y a eu un problème.
              Le nouveau commentaire n'a pas été enregistré pour l'article
              <b>$title</b>.<br />
              Le résultat est : $result.<br />
              commentaire : <br />" . $comment;
        } else {
          $returnQuery["alertType"] = "alert-success";
          $returnQuery["alert"] = "Nouveau commentaire enregistré pour l'article <b>" . $title . "</b>";
        }

      } else {   // title referenced in db, update the comment :
        echo "Updated comment for article $title :";
        $writeFailed = $db->querySingle(
            "UPDATE articles SET comment = '" . SQLite3::escapeString($comment) . "' WHERE title = '" . $title . "';"
        );
        if ($writeFailed) {
          $returnQuery["alertType"] = "alert-warning";
          $returnQuery["alert"] = "Il y a eu un problème.
              Le commentaire n'a pas été mis à jour pour la note <b>$title</b>";
        } else {
          $returnQuery["alertType"] = "alert-success";
          $returnQuery["alert"] = "Commentaire mis à jour pour la note <b>" . $title . "</b>";
        }
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
  <pre>
    <?php echo str_ireplace("\n", "<br />", $comment); ?>
  </pre>
  <br />
  <a href="<?php echo $end_url; ?>">revenir</a>

</body>
</html>
