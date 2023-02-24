<?php

require '_include.php';

$Parsedown = new Parsedown();

$file = $_POST['file'];
$file = str_replace('..','',$file);
$file = se_filter_filepath($file);

$docs_file = '../../docs/'.$languagePack.'/'.$file;

$file = se_parse_docs_file($docs_file);
echo json_encode($file);