<?php
require '_include.php';


$Parsedown = new Parsedown();

$file = $_POST['file'];
$file = str_replace('..','',$file);
$file = se_filter_filepath($file);

$docs_file = '../../docs/'.$languagePack.'/'.$file;

$file_contents = se_parse_docs_file($docs_file);

echo '<h3>'.$file_contents['title'].'</h3>';
echo '<div>'.$file_contents['content'].'</div>';