<?php

/**
 * router for the central "tagged" content-archive page type
 * (page_type_of_use = 'tagged') - a page with this type shows every page,
 * blog post, product and event carrying a given tag, grouped by content
 * type. Always a list - there is no single-item display mode for a tag.
 *
 * @var string $mod_slug the URL segment after this page's own permalink,
 *                        e.g. "some-tag/" for /tags/some-tag/
 */

$array_mod_slug = explode("/", $mod_slug);
$tag_name_clean = clean_filename($array_mod_slug[0] ?? '');

include __DIR__.'/tagged-list.php';
