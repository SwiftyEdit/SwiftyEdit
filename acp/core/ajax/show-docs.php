<?php

require '_include.php';

$Parsedown = new Parsedown();

$docs_file = '../../docs/'.$languagePack.'/'.basename($_POST['file']);

$file = se_parse_docs_file($docs_file);
echo json_encode($file);