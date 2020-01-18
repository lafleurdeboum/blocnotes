<?php

  chdir("../engine/");

  header("Content-Type: application/rss+xml; charset=UTF-8");

  $address = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
  // Take off index.php + 1 dir level :
  $address = preg_replace("/index.php$/", "", $address);
  $address = preg_replace("/\/\w+\/$/", "", $address);

  $rssfeed = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
  $rssfeed .= "<rss version=\"2.0\">\n";
  $rssfeed .= "<channel>\n";
  $rssfeed .= "<title>notes du bloc:</title>\n";
  $rssfeed .= "<link>" . $address . "</link>\n";
  $rssfeed .= "<description></description>\n";
  $rssfeed .= "<language>fr-fr</language>\n";
  $rssfeed .= "<copyright>Copyright (C) 2020 blocSemiColon.org</copyright>\n";
 
  $dbFile = "../admin/notes.db";
  require_once("engine.php");
  load_db();

  $query = "SELECT * FROM notes ORDER BY timestamp DESC";
  $notes = $db->query($query);
  while($row = $notes->fetchArray()) {
    extract($row);
    $link = $address . "?title=" . $title;
 
    $rssfeed .= "<item>\n";
    $rssfeed .= "<title>" . $title . "</title>\n";
    $rssfeed .= "<description>" . $comment . "</description>\n";
    $rssfeed .= "<link>" . $link . "</link>\n";
    $rssfeed .= "<pubDate>" . date("D, d M Y H:i:s O", strtotime($timestamp)) . "</pubDate>\n";
    // Crawl documents, sort images, display their thumbnail :
    $documents = $db->query("SELECT * FROM documents WHERE instr(attached_notes, ',$title,')");
    if($documents) {
      while($row = $documents->fetchArray()) {
        extract($row);
        $mediatype = explode("/", $filetype)[0];
        if($mediatype == "image") {
          try {
            $rssfeed .= "<enclosure url='$address/documents/thumbnails/$filename' length='" . filesize("../documents/thumbnails/".$filename) . "' type='$filetype' />\n";
          } catch (Throwable $error) {  }
        }
      }
    }
    $rssfeed .= "</item>\n";
  }
 
  $rssfeed .= "</channel>\n";
  $rssfeed .= "</rss>\n";
 
  echo $rssfeed;

