<?php

$subinc = match (true) {
    str_starts_with($query, 'inbox/mail/') => 'inbox-mail',
    str_starts_with($query, 'inbox/comments/') => 'inbox-comments',
    str_starts_with($query, 'inbox/reactions/') => 'inbox-reactions',
    default => 'inbox-mail'
};

include $subinc.'.php';