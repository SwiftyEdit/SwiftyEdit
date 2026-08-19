<?php

/**
 * Backfill UUIDs for rows created before se_user/se_orders/se_products had a
 * UUID column. Formerly the "se_users_uuid" / "se_orders_uuid" /
 * "se_products_uuid" buttons on the manual Helpers page
 * (acp/core/update/helpers.php).
 */

return function ($db_content, $db_user, $db_posts) {
    se_helper_fill_uuids($db_user, 'se_user', 'user_id', 'user_uuid');
    se_helper_fill_uuids($db_content, 'se_orders', 'id', 'uuid');
    se_helper_fill_uuids($db_posts, 'se_products', 'id', 'uuid');
};
