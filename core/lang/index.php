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

    $json_backend = file_get_contents(SE_ROOT.'core/lang/'.$languagePack.'/backend.json');
    $data_backend = json_decode($json_backend,true);

    $json_frontend = file_get_contents(SE_ROOT.'core/lang/'.$languagePack.'/frontend.json');
    $data_frontend = json_decode($json_frontend,true);


    $json_dict = file_get_contents(SE_ROOT.'core/lang/'.$languagePack.'/dictionary.json');
    $data_dict = json_decode($json_dict,true);

    $lang_data = array_merge($data_backend,$data_frontend,$data_dict);

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