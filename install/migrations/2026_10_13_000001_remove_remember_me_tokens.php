<?php

/**
 * Remove the stored tokens of the former "stay logged in" option.
 *
 * The option wrote a row into se_tokens (and two cookies) on every login
 * with the checkbox, but nothing ever read them - it never kept anybody
 * logged in. The option and the table definition are gone; existing
 * installations keep the (now unused) table, as the schema update only adds
 * tables and columns. Its rows are deleted here. The old cookies are cleared
 * on logout (se_end_user_session()) or expire on their own.
 */

return function ($db_content, $db_user, $db_posts) {
    if (table_exists('user', 'se_tokens') > 0) {
        $db_user->query('DELETE FROM se_tokens');
    }
};
