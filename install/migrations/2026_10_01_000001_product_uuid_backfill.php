<?php

/**
 * Backfill product UUIDs a second time.
 *
 * se_prepareProductData() (acp/core/functions_shop.php) used to write an
 * empty uuid on every product save in the ACP, so products created or edited
 * after 2026_08_17_000002_uuid_backfill lost theirs again. The save path is
 * fixed now; this gives every product without a uuid a new one.
 *
 * Only empty uuids are filled (se_helper_fill_uuids()), so it is safe to run
 * on any installation.
 */

return function ($db_content, $db_user, $db_posts) {
    se_helper_fill_uuids($db_posts, 'se_products', 'id', 'uuid');
};
