<?php

/**
 * @var string $languagePack en | de | es ...
 */

if(SE_SECTION == 'frontend') {
    // default include en
    include SE_ROOT.'lib/lang/en/dict-frontend.php';

    $language_file = SE_ROOT.'lib/lang/'.$languagePack.'/dict-frontend.php';

    if(is_file($language_file)) {
        include $language_file;
    }

	$extend_lf = SE_CONTENT.'/plugins/lang_'.$languagePack.'.php';
} elseif(SE_SECTION == 'backend') {
	include SE_ROOT.'lib/lang/en/dict-backend.php';
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