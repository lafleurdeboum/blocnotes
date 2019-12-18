<?php

  // TODO Check if we have write acces to the DB, otherwise we don't
  //      get proper error report.

  $pool = "documents";
  $dbFile = "admin/articles.db";
  $returnObject = array();
  $referer = parse_url($_SERVER['HTTP_REFERER']);
  $title = $_POST['title'];

  if (! extension_loaded('sqlite3')) {
    $alertType = "alert-warning";
    $alert = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
  }
  $db = new SQLite3($dbFile);

  $raw_comment = $db->querySingle(
      "SELECT comment FROM articles WHERE title = '$title';"
  );

  // Turn comment into html using markdown :
  require_once 'Markdown/Markdown.inc.php';
  use Michelf\Markdown;

  if($raw_comment == "") {
    $comment = "";
    $alertType = "alert-success";
    $alert = "\nnote vide : <b>$title</b>";
  } else {
    $comment = Markdown::defaultTransform($raw_comment);
    /*
    // Separate comment into sections around h2 headers :

    $commentList = explode("<h2>", $comment);
    // Create a section on each header size 2 :
    if(substr_count($commentList[0], "</h2>")) {
      // Then comment began with "<h2>".
      $displayable = "<section><h2>";
    } else {
      $displayable = "<section>";
    }
    $displayable .= implode("</section><section><h2>", $commentList);
    $displayable .= "</section>";
    // Erase any empty section generated :
    $displayable = str_ireplace("<section><h2></section>", "", $displayable);
    $displayable = str_ireplace("<section></section>", "", $displayable);
    $comment = $displayable;
     */

    $alertType = "alert-success";
    $alert = "note chargée : <b>" . $title . "</b>";
  }

  // List articles in db :
  $articleList = array();
  $articles = $db->query("SELECT * FROM articles");
  while ($article = $articles->fetchArray()) {
    array_push($articleList, $article['title']);
  }

  $returnObject["alert"] = $alert;
  $returnObject["alertType"] = $alertType;
  $returnObject["title"] = $title;
  $returnObject["comment"] = $comment;
  $returnObject["raw_comment"] = $raw_comment;
  $returnObject["articles"] = $articleList;

  echo json_encode($returnObject);

