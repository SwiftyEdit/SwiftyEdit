<?php

/**
 * Prepare se_logs for the structured activity log (see se_log_content()).
 *
 * 1. Older "user changed mail" entries lose the address they contained.
 * 2. Every content item edited in the last 30 days gets one entry from its
 *    lastedit column, so the dashboard doesn't start empty. Whether it was new
 *    or changed isn't known anymore, so all of them count as "update". Old free
 *    text entries are not parsed.
 *
 * The functions from app/functions/ aren't available on the /install/ update
 * path, so the entries are built here. Oldest first, because se_cleanup_logs()
 * looks at the lowest id.
 */

return function ($db_content, $db_user, $db_posts) {

    $db_content->query("UPDATE se_logs SET entry = 'user changed mail' WHERE entry LIKE 'user changed mail - %'");

    $since = time() - (30 * 86400);
    $items = [];

    $add = static function (string $type, ?int $id, $time, string $title, string $key, $source) use (&$items, $since): void {
        if ((int) $time < $since) {
            return;
        }
        $items[] = [
            'type' => $type,
            'id' => $id,
            'time' => (int) $time,
            'label' => mb_substr(html_entity_decode($title, ENT_QUOTES, 'UTF-8'), 0, 255),
            'key' => $key,
            'source' => (string) $source,
        ];
    };

    foreach ($db_content->select('se_pages', ['page_id', 'page_title', 'page_lastedit', 'page_lastedit_from']) as $row) {
        $add('page', (int) $row['page_id'], $row['page_lastedit'], (string) $row['page_title'], '', $row['page_lastedit_from']);
    }

    // se_snippets also holds the shop's features and options
    $snippets = $db_content->select('se_snippets', ['snippet_id', 'snippet_name', 'snippet_title', 'snippet_lastedit', 'snippet_lastedit_from'], [
        'snippet_type' => ['snippet', 'snippet_core']
    ]);
    foreach ($snippets as $row) {
        $add('snippet', (int) $row['snippet_id'], $row['snippet_lastedit'], (string) $row['snippet_title'], (string) $row['snippet_name'], $row['snippet_lastedit_from']);
    }

    foreach ($db_posts->select('se_posts', ['post_id', 'post_title', 'post_lastedit', 'post_lastedit_from']) as $row) {
        $add('post', (int) $row['post_id'], $row['post_lastedit'], (string) $row['post_title'], '', $row['post_lastedit_from']);
    }

    foreach ($db_posts->select('se_products', ['id', 'title', 'product_variant_title', 'lastedit', 'lastedit_from']) as $row) {
        $add('product', (int) $row['id'], $row['lastedit'], (string) ($row['product_variant_title'] ?: $row['title']), '', $row['lastedit_from']);
    }

    foreach ($db_posts->select('se_events', ['id', 'title', 'lastedit', 'lastedit_from']) as $row) {
        $add('event', (int) $row['id'], $row['lastedit'], (string) $row['title'], '', $row['lastedit_from']);
    }

    // one row per file and language - one entry per file, the newest edit
    $media = [];
    foreach ($db_content->select('se_media', ['media_file', 'media_lastedit', 'media_upload_from']) as $row) {
        $file = (string) $row['media_file'];
        if (!isset($media[$file]) || (int) $row['media_lastedit'] > (int) $media[$file]['media_lastedit']) {
            $media[$file] = $row;
        }
    }
    foreach ($media as $file => $row) {
        $file = (string) $file;
        $add('media', null, $row['media_lastedit'], basename($file), $file, $row['media_upload_from']);
    }

    usort($items, static fn($a, $b) => $a['time'] <=> $b['time']);

    foreach ($items as $item) {
        $entry = 'update '.$item['type'];
        if ($item['id'] !== null) {
            $entry .= ' #'.$item['id'];
        }
        if ($item['label'] !== '' || $item['key'] !== '') {
            $entry .= ': '.($item['label'] !== '' ? $item['label'] : $item['key']);
        }

        $db_content->insert('se_logs', [
            'time' => (string) $item['time'],
            'source' => $item['source'] !== '' ? $item['source'] : 'system',
            'entry' => mb_substr($entry, 0, 255),
            'priority' => 3,
            'action' => 'update',
            'object_type' => $item['type'],
            'object_id' => $item['id'],
            'object_key' => $item['key'] !== '' ? $item['key'] : null,
            'object_label' => $item['label'],
        ]);
    }
};
