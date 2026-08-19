<?php

/**
 * Core tags feature.
 *
 * A lightweight, language-independent taxonomy shared across pages, blog
 * posts, products and events. Unlike se_categories (a heavyweight
 * landing-page object referenced via a delimited string of cat_hash values),
 * tags are plain name/slug rows linked to content through the
 * se_tags_relations junction table - a real join instead of a LIKE scan,
 * and a real cascade delete instead of orphaned references.
 */


/**
 * path to the tags cache file
 * cache directory is created on first use
 */
function se_getTagsCachePath() {

    $dir = SE_CONTENT . '/cache/tags';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    return $dir . '/tags.json';
}

/**
 * get all tags
 * order by tag_name
 * reads from cache when available, falls back to the database
 */
function se_get_tags() {
    global $db_content;

    $cache_file = se_getTagsCachePath();
    if (file_exists($cache_file) && is_readable($cache_file)) {
        $content = file_get_contents($cache_file);
        if ($content !== false) {
            $tags = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $tags;
            }
        }
    }

    return $db_content->select("se_tags", "*", [
        "ORDER" => ["tag_name" => "ASC"]
    ]);
}

/**
 * rebuild the tags cache file from the database
 * called after any tag create/rename/delete or content re-tagging
 */
function se_updateTagsCache() {
    global $db_content;

    $tags = $db_content->select("se_tags", "*", [
        "ORDER" => ["tag_name" => "ASC"]
    ]);

    $cache_file = se_getTagsCachePath();
    file_put_contents($cache_file, json_encode($tags, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * tags currently attached to one content item
 *
 * @param string $type 'page' | 'post' | 'product' | 'event'
 * @param int $id
 * @return array list of se_tags rows
 */
function se_get_content_tags(string $type, int $id): array {
    global $db_content;

    $relations = $db_content->select("se_tags_relations", "tag_id", [
        "content_type" => $type,
        "content_id" => $id
    ]);

    if (empty($relations)) {
        return [];
    }

    $all_tags = se_get_tags();
    $tags = [];
    foreach ($all_tags as $tag) {
        if (in_array($tag['tag_id'], $relations)) {
            $tags[] = $tag;
        }
    }

    return $tags;
}

/**
 * comma-joined tag names for one content item, ready to prefill the
 * "form-control tags" (Selectize) input on an edit form
 */
function se_tags_csv_for_content(string $type, int $id): string {

    $tags = se_get_content_tags($type, $id);
    $names = array_map(fn($tag) => $tag['tag_name'], $tags);

    return implode(', ', $names);
}

/**
 * replace the tags attached to one content item
 *
 * Accepts free-text tag names (as submitted from the "form-control tags"
 * input), creates any tag that doesn't exist yet (matched case-insensitively
 * via tag_name_clean), then replaces (delete-all, re-insert) that content
 * item's relations.
 *
 * @param string $type 'page' | 'post' | 'product' | 'event'
 * @param int $id
 * @param array $tagNames raw, comma-split tag names as submitted
 */
function se_set_content_tags(string $type, int $id, array $tagNames): void {
    global $db_content;

    $existing_tags = se_get_tags();

    $tag_ids = [];
    foreach ($tagNames as $raw_name) {
        $tag_name = sanitizeUserInputs($raw_name);
        if ($tag_name === '') {
            continue;
        }
        $tag_name_clean = clean_filename($tag_name);
        if ($tag_name_clean === '') {
            continue;
        }

        $tag_id = null;
        foreach ($existing_tags as $tag) {
            if ($tag['tag_name_clean'] === $tag_name_clean) {
                $tag_id = $tag['tag_id'];
                break;
            }
        }

        if ($tag_id === null) {
            $db_content->insert("se_tags", [
                "tag_name" => $tag_name,
                "tag_name_clean" => $tag_name_clean
            ]);
            $tag_id = $db_content->id();
            // keep the in-memory list in sync in case the same new tag is
            // submitted more than once in this request
            $existing_tags[] = ["tag_id" => $tag_id, "tag_name" => $tag_name, "tag_name_clean" => $tag_name_clean];
        }

        if (!in_array($tag_id, $tag_ids)) {
            $tag_ids[] = $tag_id;
        }
    }

    $db_content->delete("se_tags_relations", [
        "content_type" => $type,
        "content_id" => $id
    ]);

    foreach ($tag_ids as $tag_id) {
        $db_content->insert("se_tags_relations", [
            "tag_id" => $tag_id,
            "content_type" => $type,
            "content_id" => $id
        ]);
    }

    se_updateTagsCache();
}

/**
 * delete a tag and cascade-delete its relations
 * (deliberately not left orphaned, unlike se_categories on delete)
 */
function se_delete_tag(int $tagId): void {
    global $db_content;

    $db_content->delete("se_tags_relations", [
        "tag_id" => $tagId
    ]);
    $db_content->delete("se_tags", [
        "tag_id" => $tagId
    ]);

    se_updateTagsCache();
}

/**
 * merge one tag into another: every relation the source tag has gets
 * repointed to the target tag (skipped, not duplicated, if the target
 * already has that exact relation), then the source tag is deleted.
 *
 * Used when renaming a tag to a name that collides with a different,
 * already-existing tag (e.g. "Flyers" -> "Flyer") - without this, a plain
 * rename would leave two se_tags rows with the same tag_name_clean, and
 * lookups by slug (se_get_content_ids_by_tag() etc.) only ever find one of
 * them, silently hiding whichever tag's content lost the lookup.
 */
function se_merge_tag(int $sourceTagId, int $targetTagId): void {
    global $db_content;

    if ($sourceTagId === $targetTagId) {
        return;
    }

    $source_relations = $db_content->select("se_tags_relations", ["content_type", "content_id"], [
        "tag_id" => $sourceTagId
    ]);

    foreach ($source_relations as $rel) {
        $target_has_it = $db_content->get("se_tags_relations", ["id"], [
            "tag_id" => $targetTagId,
            "content_type" => $rel['content_type'],
            "content_id" => $rel['content_id']
        ]);

        if (empty($target_has_it)) {
            $db_content->update("se_tags_relations", [
                "tag_id" => $targetTagId
            ], [
                "tag_id" => $sourceTagId,
                "content_type" => $rel['content_type'],
                "content_id" => $rel['content_id']
            ]);
        } else {
            // target is already linked to this content item - drop the
            // now-redundant source relation instead of duplicating it
            $db_content->delete("se_tags_relations", [
                "tag_id" => $sourceTagId,
                "content_type" => $rel['content_type'],
                "content_id" => $rel['content_id']
            ]);
        }
    }

    $db_content->delete("se_tags", [
        "tag_id" => $sourceTagId
    ]);

    se_updateTagsCache();
}

/**
 * all tags with their usage count, for the ACP tags list
 */
function se_get_tags_with_usage(): array {
    global $db_content;

    $tags = se_get_tags();

    $relations = $db_content->select("se_tags_relations", "tag_id");
    $counts = array_count_values($relations);

    foreach ($tags as &$tag) {
        $tag['tag_usage'] = $counts[$tag['tag_id']] ?? 0;
    }
    unset($tag);

    return $tags;
}

/**
 * content ids of one type currently carrying a given tag (by slug)
 * used to filter content lists by tag
 *
 * @param string $type 'page' | 'post' | 'product' | 'event'
 * @param string $tagNameClean
 * @return array list of content_id
 */
function se_get_content_ids_by_tag(string $type, string $tagNameClean): array {
    global $db_content;

    $tag = $db_content->get("se_tags", ["tag_id"], [
        "tag_name_clean" => $tagNameClean
    ]);

    if (empty($tag)) {
        return [];
    }

    return $db_content->select("se_tags_relations", "content_id", [
        "content_type" => $type,
        "tag_id" => $tag['tag_id']
    ]);
}

/**
 * all published content tagged with a given slug, grouped by content type -
 * the data source for the central "tagged" archive page.
 *
 * Reuses the per-type entry functions (which already understand
 * $filter['tag'] via se_get_content_ids_by_tag()) instead of a cross-table
 * SQL join - $db_content and $db_posts are separate Medoo connections, so a
 * single query across pages/posts/products/events isn't possible anyway.
 *
 * @return array{pages: array, posts: array, products: array, events: array}
 */
function se_get_content_by_tag(string $tagNameClean, string $language): array {
    global $db_content;

    $page_ids = se_get_content_ids_by_tag('page', $tagNameClean);
    $pages = [];
    if (!empty($page_ids)) {
        $pages = $db_content->select("se_pages", "*", [
            "page_id" => $page_ids,
            "page_status" => "public",
            "page_language" => $language
        ]);
    }

    // se_get_post_entries()/se_get_event_entries() always glom pagination
    // stats onto $entries[0], even when there are zero real matches (unlike
    // se_get_products(), which guards this with count($entries) > 0) - so an
    // empty match set still comes back as one "row" containing only cnt_*
    // keys. Detect that and normalize to a real empty array.
    $posts = se_get_post_entries(0, 'all', [
        'text' => '',
        'languages' => $language,
        'types' => 'm-i-g-v-l',
        'status' => '1',
        'categories' => '',
        'tag' => $tagNameClean
    ]);
    if (empty($posts[0]['post_id'])) {
        $posts = [];
    }

    $products = se_get_products(0, 'all', [
        'languages' => $language,
        'status' => '1',
        'categories' => '',
        'tag' => $tagNameClean
    ]);

    $events = se_get_event_entries(0, 'all', [
        'text_search' => '',
        'languages' => $language,
        'status' => '1',
        'categories' => '',
        'tag' => $tagNameClean
    ]);
    if (empty($events[0]['id'])) {
        $events = [];
    }

    return [
        'pages' => $pages,
        'posts' => $posts,
        'products' => $products,
        'events' => $events
    ];
}

/**
 * permalink of the page with a given page_type_of_use in a given language
 * (e.g. the Blog listing page for 'display_post'), or null if none exists.
 * Mirrors the lookup already used inline in posts-list.php/events-list.php -
 * with one difference: those callers fall back to $swifty_slug (the current
 * page) when no match is found, which only works because they're always
 * invoked from within the blog/shop/events page itself. Called from
 * elsewhere (e.g. the tag archive page), that fallback would point at the
 * wrong page, so this returns null instead and additionally falls back to
 * the page_posts_types convention - in practice, most sites mark their
 * blog/shop/events listing page that way (a comma list of post-type
 * letters) rather than via page_type_of_use=display_X.
 */
function se_get_type_of_use_page_permalink(string $typeOfUse, string $language): ?string {
    global $db_content;

    $target_page = $db_content->select("se_pages", "page_permalink", [
        "AND" => [
            "page_type_of_use" => $typeOfUse,
            "page_language" => $language
        ]
    ]);

    if (!empty($target_page) && $target_page[0] != '') {
        return $target_page[0];
    }

    $posts_types_letter = match ($typeOfUse) {
        'display_post' => 'm',
        'display_product' => 'p',
        'display_event' => 'e',
        default => null
    };

    if ($posts_types_letter === null) {
        return null;
    }

    $target_page = $db_content->select("se_pages", "page_permalink", [
        "page_posts_types[~]" => $posts_types_letter,
        "page_language" => $language
    ]);

    if (empty($target_page) || $target_page[0] == '') {
        return null;
    }

    return $target_page[0];
}

/**
 * frontend URL of the central "tagged" archive page for a given language,
 * or null if no such page has been created yet in the ACP - callers should
 * fall back to a local ?tag= filter link in that case.
 */
function se_get_tagged_page_url(string $tagNameClean, string $language): ?string {
    $permalink = se_get_type_of_use_page_permalink('tagged', $language);
    if ($permalink === null) {
        return null;
    }

    return '/' . $permalink . $tagNameClean . '/';
}
