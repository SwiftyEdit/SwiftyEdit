<?php

if(SE_SECTION == 'frontend') {
	include $languagePack.'/dict-frontend.php';
	$extend_lf = SE_CONTENT.'/plugins/lang_'.$languagePack.'.php';
} elseif(SE_SECTION == 'backend') {
	include 'en/dict-backend.php';
	if($languagePack != 'en') {
		include $languagePack.'/dict-backend.php';
	}
	$extend_lf = '../' . SE_CONTENT.'/plugins/lang_'.$languagePack.'.php';
} else {
	die();
}

if(is_file($extend_lf)) {
	include $extend_lf;
}