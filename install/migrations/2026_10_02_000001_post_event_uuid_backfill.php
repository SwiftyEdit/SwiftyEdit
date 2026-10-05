<?php

/**
 * Backfill UUIDs for blog posts and events.
 *
 * Neither was part of 2026_08_17_000002_uuid_backfill, and the ACP never set
 * them: the blog writer (acp/core/blog/data-writer.php) wrote an empty
 * post_uuid on every save, the event writer (acp/core/events/data-writer.php)
 * never set uuid for new events. Both save paths are fixed now; this gives
 * every post and event without a uuid a new one.
 *
 * Only empty uuids are filled (se_helper_fill_uuids()), so it is safe to run
 * on any installation.
 */

return function ($db_content, $db_user, $db_posts) {
    se_helper_fill_uuids($db_posts, 'se_posts', 'post_id', 'post_uuid');
    se_helper_fill_uuids($db_posts, 'se_events', 'id', 'uuid');
};
