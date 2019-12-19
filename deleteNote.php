<?php

  $pool = "documents";
  $dbFile = "admin/articles.db";
  $returnObject = array();
  $referer = parse_url($_SERVER['HTTP_REFERER']);
  $title = $_GET['title'];

  if (! extension_loaded('sqlite3')) {
    $alertType = "alert-warning";
    $alert = "Pas de support sqlite3 ! Accès impossible à la base de données <b>$dbFile</b>";
  }
  $db = new SQLite3($dbFile);

  $result = $db->exec("DELETE FROM articles WHERE title = '$title';");
  $alertType = "alert-success";
  $alert = "note $title supprimée : <b>$result</b>";

  // List articles in db :
  $articleList = array();
  $articles = $db->query("SELECT * FROM articles");
  while ($article = $articles->fetchArray()) {
    array_push($articleList, $article['title']);
  }

  $returnObject["alert"] = $alert;
  $returnObject["alertType"] = $alertType;
  $returnObject["title"] = $title;
  $returnObject["articles"] = $articleList;

  echo json_encode($returnObject);

