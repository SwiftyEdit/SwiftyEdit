<?php

/**
 * @var string $languagePack en | de | es ...
 */

if(SE_SECTION == 'frontend') {

    $lang_file_frontend = SE_ROOT.'core/lang/en/frontend.json';
    $lang_file_dict = SE_ROOT.'core/lang/en/dictionary.json';
    if(is_file(SE_ROOT.'core/lang/'.$languagePack.'/frontend.json')) {
        $lang_file_frontend = SE_ROOT.'core/lang/'.$languagePack.'/frontend.json';
    }

    if(is_file(SE_ROOT.'core/lang/'.$languagePack.'/dictionary.json')) {
        $lang_file_dict = SE_ROOT.'core/lang/'.$languagePack.'/dictionary.json';
    }

    $json_frontend = file_get_contents($lang_file_frontend);
    $json_dict = file_get_contents($lang_file_dict);
    $data_frontend = json_decode($json_frontend,true);
    $data_dict = json_decode($json_dict,true);

    $lang_data = array_merge($data_frontend,$data_dict);

    foreach($lang_data as $key => $value) {
        $lang[str_replace('.','_',$key)] = $value;
    }

	$extend_lf = SE_CONTENT.'/plugins/lang_'.$languagePack.'.php';

} elseif(SE_SECTION == 'backend') {

    $default_lang_file_backend = SE_ROOT.'core/lang/en/backend.json';
    $default_lang_file_dict = SE_ROOT.'core/lang/en/dictionary.json';

    $default_backend = l10n_to_array($default_lang_file_backend);
    $default_dict = l10n_to_array($default_lang_file_dict);

    $data_backend = l10n_to_array(SE_ROOT.'core/lang/'.$languagePack.'/backend.json');
    $data_frontend = l10n_to_array(SE_ROOT.'core/lang/'.$languagePack.'/frontend.json');
    $data_dict = l10n_to_array(SE_ROOT.'core/lang/'.$languagePack.'/dictionary.json');

    $lang_data = array_merge($default_backend,$default_dict,$data_frontend,$data_backend,$data_dict);

    foreach($lang_data as $key => $value) {
        $lang[str_replace('.','_',$key)] = $value;
    }

    $extend_lf = '../' . SE_CONTENT.'/plugins/lang_'.$languagePack.'.php';

} else {
	die();
}

if(is_file($extend_lf)) {
	include $extend_lf;
}

/**
 * @param $file
 * @return mixed
 */
function l10n_to_array($file) {
    $data = array();
    if(is_file($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
    }
    return $data;
}