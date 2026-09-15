<?php

/**
 * Backfill post_canonical_url (se_posts) and canonical_url (se_events) for
 * existing rows, analogous to
 * install/migrations/2026_08_27_000001_product_canonical_url_backfill.php.
 *
 * Both columns are new (see acp/core/blog/data-writer.php and
 * acp/core/events/data-writer.php, "canonical URL support" commits) and are
 * only computed once, at save time - existing rows that haven't been
 * resaved since have them empty. app/handlers/search.php and
 * app/xhr/search.php already fall back to the old
 * "{detail page}{slug}-{id}.html" construction when a row has no canonical
 * url, so this migration isn't required for correctness, just for giving
 * every row the same up-to-date, single-source-of-truth value the frontend
 * now prefers everywhere else.
 *
 * $se_base_url isn't reliably available here (see the product migration for
 * why), rebuilt from se_options the same way.
 */

return function ($db_content, $db_user, $db_posts) {

    $options = $db_content->select('se_options', ['option_key', 'option_value'], [
        'option_module' => 'se',
    ]);

    $settings = [];
    foreach ($options as $option) {
        if (str_starts_with($option['option_key'], 'prefs_')) {
            $settings[substr($option['option_key'], 6)] = $option['option_value'];
        }
    }

    $se_base_url = !empty($settings['cms_ssl_domain'])
        ? $settings['cms_ssl_domain'] . $settings['cms_base']
        : $settings['cms_domain'] . $settings['cms_base'];

    // Per-language page lookups, cached - mirrors the fallback order
    // acp/core/blog/data-writer.php uses for post_canonical_url ("display_post"
    // type-of-use page first, then any page accepting post_type "m").
    $post_target_pages = [];
    $resolve_post_target_page = function (string $lang) use ($db_content, &$post_target_pages) {
        if (!array_key_exists($lang, $post_target_pages)) {
            $permalink = $db_content->get('se_pages', 'page_permalink', [
                'AND' => ['page_type_of_use' => 'display_post', 'page_language' => $lang],
            ]);
            if (empty($permalink)) {
                $permalink = $db_content->get('se_pages', 'page_permalink', [
                    'AND' => ['page_posts_types[~]' => 'm', 'page_language' => $lang],
                ]);
            }
            $post_target_pages[$lang] = $permalink ?: '';
        }
        return $post_target_pages[$lang];
    };

    // mirrors acp/core/events/data-writer.php's "default" main_category_slug
    // resolution (any page accepting post_type "e").
    $event_category_pages = [];
    $resolve_event_category_page = function (string $lang) use ($db_content, &$event_category_pages) {
        if (!array_key_exists($lang, $event_category_pages)) {
            $permalink = $db_content->get('se_pages', 'page_permalink', [
                'AND' => ['page_posts_types[~]' => 'e', 'page_language' => $lang],
            ]);
            $event_category_pages[$lang] = $permalink ?: '';
        }
        return $event_category_pages[$lang];
    };

    // --- blog posts ---

    $posts = $db_posts->select('se_posts', ['post_id', 'post_slug', 'post_lang', 'post_canonical_url']);

    foreach ($posts as $post) {
        if (!empty($post['post_canonical_url'])) {
            continue;
        }

        $target_page = $resolve_post_target_page((string) $post['post_lang']);

        // basename(), not the writer's own str_replace('/', '', ...) - some
        // older posts still have a slug from before slugs stopped embedding
        // extra path segments, and basename() is what the working links on
        // the frontend blog listing (post_href in
        // app/handlers/posts-list.php) already use for those.
        $filename = basename($post['post_slug']) . '-' . $post['post_id'] . '.html';

        $db_posts->update('se_posts', [
            'post_canonical_url' => $se_base_url . $target_page . $filename,
        ], [
            'post_id' => $post['post_id'],
        ]);
    }

    // --- events ---

    $events = $db_posts->select('se_events', ['id', 'slug', 'event_lang', 'main_category_slug', 'canonical_url']);

    foreach ($events as $event) {
        if (!empty($event['canonical_url'])) {
            continue;
        }

        $main_category_slug = $event['main_category_slug'];
        if (empty($main_category_slug)) {
            $main_category_slug = $resolve_event_category_page((string) $event['event_lang']);
        }

        // see the note above on posts - basename() instead of the writer's
        // str_replace('/', '', ...), matching what event_href in
        // app/handlers/events-list.php already uses for older,
        // date-folder-style slugs (e.g. "2025/10/16/xmas/").
        $filename = basename($event['slug']) . '-' . $event['id'] . '.html';

        $db_posts->update('se_events', [
            'canonical_url' => $se_base_url . $main_category_slug . $filename,
            'main_category_slug' => $main_category_slug,
        ], [
            'id' => $event['id'],
        ]);
    }
};
