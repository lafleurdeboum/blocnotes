<?php

  $dbFile = "admin/articles.db";
  $returnObject = array();
  $referer = parse_url($_SERVER['HTTP_REFERER']);
  $title = $_POST['title'];
  $raw_comment = $_POST['comment'];

  if (! extension_loaded('sqlite3')) {
    $alertType = "alert-warning";
    $alert = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
  } else {
    $db = new SQLite3($dbFile);

    $article_exists = $db->querySingle(
        "SELECT EXISTS(SELECT comment FROM articles WHERE title = '$title');"
    );

    if ($article_exists) {
      $writeFailed = $db->querySingle(
          "UPDATE articles SET comment = '" . SQLite3::escapeString($raw_comment) . "' WHERE title = '$title';"
      );
      if ($writeFailed) {
        $alertType = "alert-warning";
        $alert = "Il y a eu un problème.
            Le commentaire n'a pas été mis à jour pour la note <b>$title</b>";
      } else {
        $alertType = "alert-success";
        $alert = "Commentaire mis à jour pour la note <b>$title</b>";
      }
    } else {   // $title is not in DB, create it :
      $result = $db->exec(
        "INSERT OR IGNORE INTO articles (title, comment) VALUES ('$title',
          '" . SQLite3::escapeString($raw_comment) . "' );"
      );
      if (! $result) {
        $alertType = "alert-warning";
        $alert = "Il y a eu un problème.
            Le nouveau commentaire n'a pas été enregistré pour l'article
            <b>$title</b>.<br />
            Le résultat est : $result.<br />
            commentaire : <br />" . $raw_comment;
      } else {
        $alertType = "alert-success";
        $alert = "Nouveau commentaire enregistré pour l'article <b>$title</b>";
      }
    }
  }

  $raw_comment = $db->querySingle(
      "SELECT comment FROM articles WHERE title = '$title';"
  );

  // Turn comment into html using markdown :
  require_once 'Markdown/Markdown.inc.php';
  use Michelf\Markdown;

  if($raw_comment == "") {
    $comment = "";
    $alertType = "alert-success";
    $alert .= "note vide : <b>$title</b>";
  } else {
    $comment = Markdown::defaultTransform($raw_comment);
  }

  $returnObject["alert"] = $alert;
  $returnObject["alertType"] = $alertType;
  $returnObject["title"] = $title;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["comment"] = $comment;

  echo json_encode($returnObject);

