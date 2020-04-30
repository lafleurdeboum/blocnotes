<?php

  header("Content-Type:'application/rss+xml';charset=UTF-8");
  //header("Content-Type:application/rss+xml");
  //header("Content-Type:'text/xml';charset='UTF-8'");
  //header("Content-Type:'text/xml'");

  header("Content-Disposition:attachment;filename=feed.rss");

  chdir("../engine/");

  $self = htmlspecialchars("http://" . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']);
  $address = preg_replace("/feed\.\w+$/", "", $self);

  $rssfeed = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $rssfeed .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
  $rssfeed .= "<channel>\n";
  $rssfeed .= "<title>notes du bloc:</title>\n";
  $rssfeed .= "<link>" . $address . "</link>\n";
  $rssfeed .= "<description></description>\n";
  $rssfeed .= "<atom:link href=\"$self\" rel=\"self\" type=\"application/rss+xml\" />\n";
  $rssfeed .= "<language>fr-fr</language>\n";
  $rssfeed .= "<copyright>Copyright (C) 2020 blocSemiColon.org</copyright>\n";
 
  require_once("engine.php");
  load_db();

  $query = "SELECT * FROM notes ORDER BY timestamp DESC";
  $notes = $db->query($query);
  while($row = $notes->fetchArray()) {
    extract($row);
    $link = $address . "?title=" . rawurlencode($title);
 
    $rssfeed .= "<item>\n";
    $rssfeed .= "<title>" . $title . "</title>\n";
    $rssfeed .= "<description>" . htmlspecialchars($comment) . "</description>\n";
    $rssfeed .= "<link>" . $link . "</link>\n";
    $rssfeed .= "<pubDate>" . date("D, d M Y H:i:s O", strtotime($timestamp)) . "</pubDate>\n";
    // Crawl documents, sort images, display their thumbnail :
    $documents = $db->query("SELECT * FROM documents WHERE instr(attached_notes, ',$title,')");
    if($documents) {
      while($row = $documents->fetchArray()) {
        extract($row);
        $thumbpath = "$address/documents/thumbnails/$filename";
        $mediatype = explode("/", $filetype)[0];
        if($mediatype == "image") {
          try {
            $rssfeed .= "<enclosure url='".rawurlencode($thumbpath)."' length='" . filesize($thumbpath) . "' type='$filetype' />\n";
          } catch (Throwable $error) {  }
        }
      }
    }
    $rssfeed .= "</item>\n";
  }
 
  $rssfeed .= "</channel>\n";
  $rssfeed .= "</rss>\n";
 
  echo $rssfeed;

