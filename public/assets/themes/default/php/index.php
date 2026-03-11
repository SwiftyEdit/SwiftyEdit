<?php

/**
 * SwiftyEdit default theme
 * custom theme functions
 *
 * function theme_text_parser() extends the basic text_parser()
 *
 * @var array $page_contents
 * @var object $smarty
 *
 */

include __DIR__.'/definitions.php';
$theme_values = json_decode($page_contents['page_template_values'],true);

function theme_text_parser($str) {

    $str = str_replace('[spacer]', '<hr class="spacer">', $str);
    $str = str_replace('[shadow]', '<hr class="hr-shadow">', $str);

	return $str;
}

if($theme_values['teaser_text'] != '') {
    $teaser_text = html_entity_decode($theme_values['teaser_text'], ENT_QUOTES | ENT_XML1, 'UTF-8');
    $smarty->assign('teaser_text', $teaser_text);
}