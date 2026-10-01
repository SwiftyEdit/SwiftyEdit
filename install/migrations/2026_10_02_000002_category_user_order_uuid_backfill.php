<?php

/**
 * Backfill UUIDs for categories, users and orders.
 *
 * None of the insert paths set a uuid: the category writer
 * (acp/core/categories/data-writer.php), user registration
 * (app/handlers/register.php), creating users in the ACP
 * (acp/core/users/data-writer.php), the installer's admin user
 * (install/php/createDB.php) and se_send_order()
 * (app/functions/functions.shop.php). Users and orders were filled once by
 * 2026_08_17_000002_uuid_backfill, but everything created afterwards has no
 * uuid; categories were never filled. All insert paths are fixed now; this
 * gives every remaining record without a uuid a new one.
 *
 * Only empty uuids are filled (se_helper_fill_uuids()), so it is safe to run
 * on any installation.
 */

return function ($db_content, $db_user, $db_posts) {
    se_helper_fill_uuids($db_content, 'se_categories', 'cat_id', 'uuid');
    se_helper_fill_uuids($db_user, 'se_user', 'user_id', 'user_uuid');
    se_helper_fill_uuids($db_content, 'se_orders', 'id', 'uuid');
};
