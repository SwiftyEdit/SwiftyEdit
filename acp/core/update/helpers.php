<?php

/**
 * database helpers after an import from SwiftyEdit 1.x in /admin/update/helpers/,
 * markup in acp/templates/update/helpers.twig, handled by the update data-writer
 *
 * Data migrations that only ever need to run once after a specific update
 * (country codes, UUID backfills, ...) are no longer manual buttons here -
 * they run automatically during the update itself, tracked in the
 * se_migrations table. See install/migrations/ and
 * se_run_pending_migrations() in install/php/functions.php.
 *
 * global variables
 * @var object $twig
 */

echo $twig->render('update/helpers.twig', [
    'writer_uri' => '/admin-xhr/update/write/'
]);
