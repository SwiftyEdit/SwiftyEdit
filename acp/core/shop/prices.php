<?php

/**
 * price groups in /admin/shop/prices/, markup in acp/templates/shop/prices.twig
 * the list and the form are loaded by the data-reader
 *
 * global variables
 * @var object $twig
 */

echo $twig->render('shop/prices.twig', [
    'reader_uri' => '/admin-xhr/shop/read/'
]);
