<!DOCTYPE html>
<html>
<head>
  <title>Morceaux Rara</title>
  <script>
    function comments_display(event) {
      var comments = document.getElementById("comments");
      comments.innerHTML = event.srcElement.response;
    }
    function play() {
      var player = document.getElementById("player");
      var xhr = new XMLHttpRequest();
      var file = arguments[0];
      var post_filename = document.getElementById("filename");
      post_filename.value = file;
      player.innerHTML = '<H3>' + file + '</H3>';
      player.innerHTML += '<audio controls><source src="Rara/' + file + '" type="audio/mpeg">Votre navigateur ne supporte pas l audio html5</audio>';
      player.innerHTML += '<div><a href="Rara/' + file + '">lien direct</a></div>';
      xhr.addEventListener("load", comments_display);
      xhr.open("GET", "read_comments.php?file=" + file);
      xhr.send();
    }
    document.addEventListener("DOMContentLoaded", function(event) {
        var xhr = new XMLHttpRequest();
        xhr.addEventListener("load", comments_display);
        xhr.open("GET", "read_comments.php?file=__main__");
        xhr.send();
      }
    );
  </script>
  <style>
    body {
      box-sizing: border-box;
      margin: 0;
    }
    #main {
      display: flex;
      max-height: 98vh;
      margin: 1vh;
    }
    #list {
      max-width: 50%;
      max-height: 100%;
      overflow-y: scroll;
      border: 1px solid black;
    }
    #top {
      max-width: 50%;
      margin: 1em;
    }
    #comments {
      margin-top: 1em;
    }
    pre {
      white-space: pre-wrap;
      font-size: 1.2em;
    }
  </style>
</head>
<body>
  <div id="main">
    <div id="list">
    <?php
      $files = array_diff(scandir('Rara'), array('.', '..'));
      foreach($files as $file) echo "<a onclick=\"play('$file')\">$file</a><br>\r\n";
    ?>
    </div>
    <div id="top">
      <div id="player"></div>
      <div id="comments">
        <h2>Morceaux Rara</h2>
      </div>
      <div id="commenter">
        <form action="post_comment.php" method="POST">
          <input id="filename" name="file" type="hidden" value="__main__" />
          <textarea name="comment" cols="40" rows="10"></textarea>
          <input type="submit" name="submit" value="Commenter">
        </form>
      </div>
    </div>
  </div>
</body>
</html>
