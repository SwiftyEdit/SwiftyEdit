<?php

/**
 * SwiftyEdit | dashboard
 *
 * @var string $query - the current url
 */

if (str_starts_with($query, 'dashboard/log/')) {
    include_once __DIR__.'/log.php';
} else {
    include_once __DIR__.'/index.php';
}
