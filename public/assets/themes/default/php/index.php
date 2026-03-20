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