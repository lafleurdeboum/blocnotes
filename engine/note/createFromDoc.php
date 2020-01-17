<?php

$createStatus = false;
$title = preg_replace("/\.\w+$/", "", $_FILES['filename']['name']);
$raw_comment = "";

require('note/create.php');
$createStatus = $status;

if($createStatus == true) {
  require('document/upload.php');
  $createStatus = $status;
} else {
  require('note/delete.php');
  $title = "";
}

// Reinitialize $documents because note/read.php will repopulate it :
$documents = array();

require('note/read.php');
$status = $createStatus;

