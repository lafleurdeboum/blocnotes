<?php

  require_once 'Markdown/Markdown.inc.php';
  use Michelf\Markdown;

    $article_exists = $db->querySingle(
        "SELECT EXISTS(SELECT comment FROM articles WHERE title = '$title');"
    );

    if ($article_exists) {
      $writeFailed = $db->querySingle(
          "UPDATE articles SET comment = '" . SQLite3::escapeString($raw_comment) . "' WHERE title = '$title';"
      );
      if ($writeFailed) {
        $messageType = "alert-warning";
        $message = "Il y a eu un problème.
            Le commentaire n'a pas été mis à jour pour la note <b>$title</b>";
      } else {
        $messageType = "alert-success";
        $message = "Commentaire mis à jour pour la note <b>$title</b>";
      }
    } else {   // $title is not in DB, create it :
      $result = $db->exec(
        "INSERT OR IGNORE INTO articles (title, comment) VALUES ('$title',
          '" . SQLite3::escapeString($raw_comment) . "' );"
      );
      if (! $result) {
        $messageType = "alert-warning";
        $message = "Il y a eu un problème.
            Le nouveau commentaire n'a pas été enregistré pour l'article
            <b>$title</b>.<br />
            Le résultat est : $result.<br />
            commentaire : <br />" . $raw_comment;
      } else {
        $messageType = "alert-success";
        $message = "Nouveau commentaire enregistré pour l'article <b>$title</b>";
      }
    }

    $raw_comment = $db->querySingle(
        "SELECT comment FROM articles WHERE title = '$title';"
    );
  
    // Turn comment into html using markdown :
    if($raw_comment == "") {
      $comment = "";
      // Only return a message if the note is empty :
      $messageType = "alert-success";
      $message .= "<br />note vide : <b>$title</b>";
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
    }

