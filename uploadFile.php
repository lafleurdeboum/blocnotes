<!DOCTYPE html>
<html>

<head>
  <!-- refuse indexing -->
  <meta name="robots" content="none" />
  <meta charset="utf-8">

  <?php

    $pool = "documents";
    $uploadFile = $pool . "/" . basename($_FILES['userfile']['name']);
    
    // Initialize the return query array with the URL's query content :
    $returnQuery = array();
    $returnURL = parse_url($_SERVER['HTTP_REFERER']);
    parse_str($returnURL["query"], $returnQuery);

    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadFile)) {
      $returnQuery["alert_type"] = "alert-success";
      $returnQuery["alert"] = "Fichier <b>" . $uploadFile . "</b> ajouté";
    } else {
      $returnQuery["alert_type"] = "alert-warning";
      $returnQuery["alert"] = "Le fichier <b>" . $uploadFile;
      $returnQuery["alert"] .= "</b> n'a pas pu être ajouté.
          Vérifiez les permissions sur le dossier.";
    }

    $end_url = $returnURL["scheme"] . "://" . $returnURL["host"];
    $end_url .= $returnURL["path"] . "?" . http_build_query($returnQuery);

  ?>

  <!-- Callback to redirect current page to caller page : -->
  <meta http-equiv="refresh" content="0; url=<?php echo $end_url ?>" />

</head>

<body id="content">

  <!-- Display committed changes in case the caller is unavailable : -->
  <p>
  File copied to <pre><?php echo $uploadFile; ?></pre>.
  </p>
  <br />
  <a href="<?php echo $end_url; ?>">revenir</a>

</body>
</html>
