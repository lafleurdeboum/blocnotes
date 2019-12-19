<?php

  require_once 'Markdown/Markdown.inc.php';
  use Michelf\Markdown;

    $raw_comment = $db->querySingle(
        "SELECT comment FROM articles WHERE title = '$title';"
    );
  
    // Turn comment into html using markdown :
    if($raw_comment == "") {
      $comment = "";
      // Only return a message if the note is empty :
      $messageType = "alert-success";
      $message = "note vide : <b>$title</b>";
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

