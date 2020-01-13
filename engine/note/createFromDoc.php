<?php

require_once 'engine.php';

require_once 'document/upload.php';

$title = preg_replace("/\.\w+$/", "", $uploadFile);
$raw_comment = "";
$comment = "";
require_once 'create.php';
// create.php already returns an answer.

