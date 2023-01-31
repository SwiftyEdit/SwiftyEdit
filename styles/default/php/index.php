<?php

/**
 * SwiftyEdit default theme
 * custom theme functions
 *
 * function theme_text_parser() extends the basic text_parser()
 *
 */

include __DIR__.'/definitions.php';

function theme_text_parser($str) {

    $str = str_replace('[spacer]', '<hr class="spacer">', $str);
    $str = str_replace('[shadow]', '<hr class="hr-shadow">', $str);

	return $str;
}