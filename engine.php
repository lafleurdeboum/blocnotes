<?php

require_once 'Markdown/Markdown.inc.php';
use Michelf\Markdown;

$dbFile = "admin/notes.db";
$action = $_POST["act"];
$title = $_POST["title"] ?: "";
$raw_comment = $_POST["raw_comment"] ?: "";
$comment = "";
$noteList = array();
$documents = array();
$message = null;
$messageType = null;

$returnObject = array();
$referer = parse_url($_SERVER['HTTP_REFERER']);

if (! extension_loaded('sqlite3')) {
  $messageType = "alert-warning";
  $message = "Pas de support sqlite3 ! Pas d'Accès à la base de données <b>$dbFile</b>";
} else {

  $db = new SQLite3($dbFile);

  function readNote() {
    global $db, $title, $raw_comment, $comment, $message, $messageType, $documents;
    $raw_comment = $db->querySingle(
        "SELECT comment FROM notes WHERE title = '$title';"
    );
    $documentQuery = $db->query(
        "SELECT filename FROM documents WHERE '$title' LIKE attached_notes;"
    );
    while ($document = $documentQuery->fetchArray()) {
        array_push($documents, $document['filename']);
    }
  }
  
  function modifyNote() {
    global $db, $title, $raw_comment, $comment, $message, $messageType;
    $article_exists = $db->querySingle(
        "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
    );
    if ($article_exists) {
      $writeFailed = $db->querySingle(
          "UPDATE notes SET comment = '" . SQLite3::escapeString($raw_comment) . "' WHERE title = '$title';"
      );
      if ($writeFailed) {
        $messageType = "alert-warning";
        $message = "Il y a eu un problème.
            Le commentaire n'a pas été mis à jour pour la note <b>$title</b>";
      } else {
        $messageType = "alert-success";
        $message = "Commentaire mis à jour pour la note <b>$title</b>";
      }
    } else {
      $messageType = "alert-warning";
      $message = "note $title non trouvée.";
    }
  }
  
  function createNote() {
    global $db, $title, $raw_comment, $comment, $message, $messageType;
    $article_exists = $db->querySingle(
        "SELECT EXISTS(SELECT comment FROM notes WHERE title = '$title');"
    );
    if ($article_exists) {
        $messageType = "alert-warning";
        $message = "la note $title existe déjà.";
    } else {
      $result = $db->exec(
        "INSERT OR IGNORE INTO notes (title, comment) VALUES ('$title',
          '" . SQLite3::escapeString($raw_comment) . "' );"
      );
      if (! $result) {
        $messageType = "alert-warning";
        $message = "Le nouveau commentaire n'a pas été enregistré pour l'article
            <b>$title</b>.<br />";
      } else {
        $messageType = "alert-success";
        $message = "Nouveau commentaire enregistré pour l'article <b>$title</b>";
      }
    }
  }
  
  function deleteNote() {
    global $db, $title, $raw_comment, $comment, $message, $messageType;
    $result = $db->exec("DELETE FROM notes WHERE title = '$title';");
    if ($result) {
      $messageType = "alert-success";
      $message = "Note <b>$title</b> supprimée";
      $title = "";
    } else {
      $messageType = "alert-warning";
      $message = "La note <b>$title</b> n'a pas pu être supprimée";
    }
  }


  switch($action) {
    case "readNote":
      readNote();
      break;
    case "createNote":
      createNote();
      break;
    case "modifyNote":
      modifyNote();
      break;
    case "deleteNote":
      deleteNote();
      break;
    default:
      $messageType = "alert-warning";
      $message = "act not understood : $action";
}

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

  // List notes in db :
  $notes = $db->query("SELECT title FROM notes;");
  if ($notes) {
    while ($note = $notes->fetchArray()) {
      array_push($noteList, $note['title']);
    }
  }
}

$returnObject["title"] = $title;
$returnObject["raw_comment"] = $raw_comment;
$returnObject["comment"] = $comment;
$returnObject["documents"] = $documents;
if ($message) {
  $returnObject["message"] = $message;
  $returnObject["messageType"] = $messageType;
}
if ($noteList) {
  $returnObject["notes"] = $noteList;
}

echo json_encode($returnObject);
echo "\n";

