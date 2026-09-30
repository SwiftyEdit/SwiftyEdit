<?php

/**
 * Rename the Greek language code from "gr" to "el".
 *
 * The Greek language pack used to live in languages/gr/ with
 * $lang_sign = "gr" - but "gr" is the ISO 3166 country code for Greece, the
 * ISO 639-1 language code is "el". The pack now lives in languages/el/, so
 * every stored "gr" language code has to follow, otherwise Greek content
 * points to a language pack that no longer exists.
 *
 * Covers:
 * - single-value language columns (pages, posts, events, products, ...)
 * - JSON translation url maps keyed by language code
 * - the default_language / deactivated_languages preferences
 */

return function ($db_content, $db_user, $db_posts) {

    $old_code = 'gr';
    $new_code = 'el';

    // --- single-value language columns ---

    $lang_columns = [
        [$db_content, 'se_pages', 'page_language'],
        [$db_content, 'se_pages_cache', 'page_language'],
        [$db_content, 'se_categories', 'cat_lang'],
        [$db_content, 'se_filter', 'filter_lang'],
        [$db_content, 'se_media', 'media_lang'],
        [$db_content, 'se_snippets', 'snippet_lang'],
        [$db_posts, 'se_posts', 'post_lang'],
        [$db_posts, 'se_events', 'event_lang'],
        [$db_posts, 'se_products', 'product_lang'],
    ];

    foreach ($lang_columns as [$db, $table, $column]) {
        $db->update($table, [
            $column => $new_code,
        ], [
            $column => $old_code,
        ]);
    }

    // --- translation urls, JSON objects keyed by language code ---
    // e.g. {"de":"/de/seite/","gr":"/gr/selida/"}
    // Plain string replacement of the key instead of decode/re-encode, so the
    // stored format (escaping, possible html entities) stays untouched.

    $key_replacements = [
        '"' . $old_code . '":' => '"' . $new_code . '":',
        '&quot;' . $old_code . '&quot;:' => '&quot;' . $new_code . '&quot;:',
    ];

    $json_columns = [
        [$db_content, 'se_pages', 'page_id', 'page_translation_urls'],
        [$db_content, 'se_pages_cache', 'page_id', 'page_translation_urls'],
        [$db_posts, 'se_products', 'id', 'translation_urls'],
    ];

    foreach ($json_columns as [$db, $table, $id_column, $column]) {
        $rows = $db->select($table, [$id_column, $column], [
            $column . '[~]' => $old_code,
        ]);

        foreach ($rows as $row) {
            $value = strtr((string) $row[$column], $key_replacements);

            if ($value !== $row[$column]) {
                $db->update($table, [
                    $column => $value,
                ], [
                    $id_column => $row[$id_column],
                ]);
            }
        }
    }

    // --- preferences ---

    $db_content->update('se_options', [
        'option_value' => $new_code,
    ], [
        'option_module' => 'se',
        'option_key' => 'prefs_default_language',
        'option_value' => $old_code,
    ]);

    $deactivated = $db_content->get('se_options', ['option_id', 'option_value'], [
        'option_module' => 'se',
        'option_key' => 'prefs_deactivated_languages',
    ]);

    if (!empty($deactivated['option_value'])) {
        $deactivated_langs = json_decode($deactivated['option_value'], true);

        if (is_array($deactivated_langs) && in_array($old_code, $deactivated_langs, true)) {
            $deactivated_langs = array_map(
                fn ($lang) => $lang === $old_code ? $new_code : $lang,
                $deactivated_langs
            );

            $db_content->update('se_options', [
                'option_value' => json_encode(array_values(array_unique($deactivated_langs))),
            ], [
                'option_id' => $deactivated['option_id'],
            ]);
        }
    }
};
