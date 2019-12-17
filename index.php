<!DOCTYPE html>
<html>
<head>

        <?php

          $pool = "documents";
          $dbFile = "admin/articles.db";
          $title = $_GET['title'];
          $alertType = $_GET['alertType'];
          $alert = $_GET['alert'];
          $newnote = $_GET['newnote'];

          // Load sqlite DB :

          if (! extension_loaded('sqlite3')) {
            $alertType = "alert-warning";
            $alert .= "Pas de support sqlite3 ! Accès impossible à la base de données <b>" . $dbFile . "</b>";
          }
          $db = new SQLite3($dbFile);

          // Load comment from db :

          $raw_comment = $db->querySingle(
              "SELECT comment FROM articles WHERE title = '$title';"
          );

          // Turn comment into html using markdown :

          require_once 'Markdown/Markdown.inc.php';
          use Michelf\Markdown;
          if($raw_comment == "") {
            $comment = "";
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

        ?>

  <!-- refuse indexing -->
  <meta name="robots" content="none" />
  <meta charset="utf-8">

  <title>Bloc Notes</title>

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
      integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
      crossorigin="anonymous">

  <script>
    function discard(event) {
      var newNoteButton = document.getElementById("validateButton");
      newNoteButton.style.visibility = "visible";
      newNoteButton.type = "button";
      newNoteButton.onclick = newNote;
      var modButton = document.getElementById("modifyToggle");
      modButton.onclick = modifyComment;
      modButton.textContent = "Modifier";
      modButton.class = "btn btn-primary";
      var comments = document.getElementById("comments");
      comments.style.display = "block";
      var commenter = document.getElementById("commenter");
      commenter.style.display = "none";
    }
    function modifyComment(event) {
      var saveButton = document.getElementById("validateButton");
      saveButton.style.visibility = "visible";
      saveButton.type = "input";
      saveButton.name = "submit";
      saveButton.textContent = "Enregistrer";
      saveButton.onclick = "";
      saveButton.form = postComment;
      var modButton = document.getElementById("modifyToggle");
      modButton.onclick = discard;
      modButton.textContent = "Annuler";
      modButton.class = "btn btn-secondary";
      var comments = document.getElementById("comments");
      comments.style.display = "none";
      var commenter = document.getElementById("commenter");
      commenter.style.display = "block";
      var title = document.getElementById("titleField");
      title.type = "hidden";
      title.textContent = "<?php echo $title; ?>";
      var commentField = document.getElementById("commentField");
      commentField.textContent = `<?php echo $raw_comment; ?>`;
    }
    function newNote(event) {
      var saveButton = document.getElementById("validateButton");
      saveButton.style.visibility = "visible";
      saveButton.type = "input";
      saveButton.name = "submit";
      saveButton.textContent = "Enregistrer";
      saveButton.onclick = "";
      saveButton.form = postComment;
      var modButton = document.getElementById("modifyToggle");
      modButton.onclick = discard;
      modButton.textContent = "Annuler";
      modButton.class = "btn btn-secondary";
      var comments = document.getElementById("comments");
      comments.style.display = "none";
      var commenter = document.getElementById("commenter");
      commenter.style.display = "block";
      var title = document.getElementById("titleField");
      title.type = "text";
      title.textContent = "";
      var comment = document.getElementById("commentField");
      comment.textContent = "";
    }
    document.addEventListener("DOMContentLoaded", function(event) {
      // Do something useful on load.
      validateButton = document.getElementById("validateButton");
      if (! "<?php echo $title ?>") {
          validateButton.style.visibility = "visible";
      } else {
          validateButton.style.visibility = "hidden";
      }
    });
  </script>
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
  
    <nav class="navbar navbar-dark bg-dark">

      <span class="">
        <a class='navbar-brand' href='.'>
          Bloc Notes
        </a>
        <?php
          if ($title) {
            echo "- $title";
          } else {
          }
        ?>

        <button onclick='modifyComment();' type="button" class="btn btn-secondary" id="modifyToggle">Modifier</button>
      </span>

      <span class="">
        <button onclick='newNote();' type='button' id='validateButton' class='btn btn-secondary' style="visibility: hidden;">nouvelle note</button>
<!--
        <button type="submit" name="submit" id="saveButton" form="postComment" class="btn btn-primary" style="visibility: hidden;">Enregistrer</button>
-->
        <input type="submit" name="submit" class="btn btn-primary" value="Supprimer" form="deleteComment">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </span>

      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <ul class="navbar-nav nav">
  
          <?php
  
            // List articles in db :
            $articles = $db->query("SELECT * FROM articles");
            while ($article = $articles->fetchArray()) {
                $listTitle = "{$article['title']}";
                echo "<li class='nav-item'><a href='?title=$listTitle'>";
                echo "$listTitle</a></li>";
            }
  
          ?>

        </ul>
      </div>

    </nav>

    <div id="main" class="container">

        <?php

          // Display possible alerts :

          if ( $alert ) {
            echo '
            <div class="alert ' . $alertType . ' alert-dismissible fade show" role="alert">
              ' . $alert . '
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            ';
          }

          // Display comment :

          echo '<div id="comments" class="container">';
          echo '<div id="commentSections">';
          echo $comment;
          echo '</div>';

          echo '</div>';

        ?>

      <!-- Hidden div with the delete prototype for current article : -->
      <div class="container">
        <form action="deleteComment.php" id="deleteComment" method="POST">
          <input id="deltitle" name="title" type="hidden" value="<?php echo $title; ?>" />
        </form>
      </div>

      <!-- Hidden div with the modifiable raw comment : -->
      <div id="commenter" class="container">
        <form action="postComment.php" id='postComment' method="POST">
          <input id="titleField" name="title" value="<?php echo $title; ?>" />

          <textarea id="commentField" name="comment"><?php echo $raw_comment; ?></textarea>
        </form>
      </div>

      <!-- Form to add files to documents folder : -->
      <?php

        if ("$title") {
          echo '
            <div class="container">
              <form action="uploadFile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <label for="userfile">ajouter un fichier :</label>
                <input type="file" id="file" name="userfile" multiple style="max-width: 80vw; overflow: scroll;" />
                <input type="submit" class="btn btn-primary" name="submit" value="Ajouter" />
            </form>
           </div>
          ';
        }

      ?>

  <!-- Optional JavaScript : -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

</body>
</html>

