<?php

require('document/upload.php');

$title = preg_replace("/\.\w+$/", "", $uploadFile);
$raw_comment = "";
$comment = "";

require('note/create.php');

