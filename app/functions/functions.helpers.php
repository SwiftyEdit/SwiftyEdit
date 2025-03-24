<?php

/**
 * returns all files and directories
 * return array()
 */

function se_scandir_recursive($dir): array {
    $result = [];
    $root = scandir($dir);
    foreach($root as $value) {
        if($value === '.' || $value === '..'|| $value === '.DS_Store') {continue;}
        $result[]="$dir/$value";
        if(is_dir("$dir/$value")) {
            foreach(se_scandir_recursive("$dir/$value") as $value) {
                $result[]=$value;
            }
        }
    }
    if(is_array($result)) {
        $result = array_filter($result);
    }

    return $result;
}

function se_covert_big_int(int $number): string {

    if ($number < 1000) {
        return sprintf('%d', $number);
    }

    if ($number < 1000000) {
        return sprintf('%d%s', floor($number / 1000), 'K+');
    }

    if ($number >= 1000000) {
        return sprintf('%d%s', floor($number / 1000000), 'M+');
    }

    return $number;
}
