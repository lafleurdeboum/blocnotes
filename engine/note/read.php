<?php

require_once 'engine.php';

load_db();

$raw_comment = $db->querySingle(
    "SELECT comment FROM notes WHERE title = '$title';"
);

get_comment();
get_note_list();
get_document_list();

return_answer();

