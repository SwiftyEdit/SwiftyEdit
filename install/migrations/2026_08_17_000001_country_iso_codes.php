<?php

/**
 * Migrate country fields from free-text ("Deutschland", "Germany", ...) to
 * ISO 3166-1 alpha-2 codes ("DE"). Formerly the "se_users_country" /
 * "se_delivery_areas_country" buttons on the manual Helpers page
 * (acp/core/update/helpers.php).
 */

return function ($db_content, $db_user, $db_posts) {

    $migration_map = se_get_country_migration_map();

    // se_user: billing + shipping address country
    $users = $db_user->select('se_user', ['user_id', 'ba_country', 'sa_country']);

    foreach ($users as $user) {
        $ba_country = $migration_map[$user['ba_country']] ?? $user['ba_country'];
        $sa_country = $migration_map[$user['sa_country']] ?? $user['sa_country'];

        if ($ba_country !== $user['ba_country'] || $sa_country !== $user['sa_country']) {
            $db_user->update('se_user', [
                'ba_country' => $ba_country,
                'sa_country' => $sa_country,
            ], [
                'user_id' => $user['user_id'],
            ]);
        }
    }

    // se_delivery_areas: country name -> code
    $areas = $db_content->select('se_delivery_areas', ['id', 'name', 'code']);

    foreach ($areas as $area) {
        $code = $migration_map[$area['name']] ?? null;

        if ($code && $code !== $area['code']) {
            $db_content->update('se_delivery_areas', [
                'code' => $code,
            ], [
                'id' => $area['id'],
            ]);
        }
    }
};
