<?php

$subinc = match (true) {
    str_starts_with($query, 'inbox/mail/edit') => 'mail-edit',
    str_starts_with($query, 'inbox/mail/new') => 'mail-edit',
    str_starts_with($query, 'inbox/mail/') => 'mail',
    str_starts_with($query, 'inbox/comments/') => 'comments',
    str_starts_with($query, 'inbox/reactions/') => 'reactions',
    default => 'mail'
};

include __DIR__.'/'.$subinc.'.php';