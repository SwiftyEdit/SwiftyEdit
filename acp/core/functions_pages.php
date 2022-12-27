<?php

require __DIR__.'/access.php';

/**
 * @param $id page id
 * @return int 0 or 1
 */
function se_delete_page($id) {

    if(!is_numeric($id)) {
        return 0;
    }



}