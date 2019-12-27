<?php

require_once 'engine.php';

global $db, $title, $raw_comment, $comment, $message, $messageType, $documents, $noteList;

load_db();

$raw_comment = $db->querySingle(
    "SELECT comment FROM notes WHERE title = '$title';"
);

get_comment();
get_note_list();
get_document_list();

return_answer();

