<?php

require('document/upload.php');

if($fileInserted) {
  $title = preg_replace("/\.\w+$/", "", $uploadFile);
  $raw_comment = "";
  //$comment = "";

  require('note/create.php');
} else {
  require('note/read.php');
}

