<?php

/**
 * SwiftyEdit - backend routing
 *
 * include data writer or
 * include backend or
 * include login
 */

session_start();
error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);


if($_SESSION['user_class'] == 'administrator') {


    /*
    if(str_contains($_REQUEST['query'] , '/data/')){
        include '../acp/data.php';
        exit;
    }
    */

    if(str_contains($_REQUEST['query'] , '/read/')){
        include '../acp/data_reader.php';
        exit;
    }

    if(str_contains($_REQUEST['query'] , '/write/')){
        include '../acp/data_writer.php';
        exit;
    }

    if(str_contains($_REQUEST['query'] , '/delete/')){
        include '../acp/data_writer.php';
        exit;
    }

    if(str_contains($_REQUEST['query'] , 'upload/')){
        include '../acp/core/xhr/upload.php';
        exit;
    }


    include '../acp/index.php';
} else {
    include '../acp/login.php';
}
