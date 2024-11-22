<?php

$subinc = match (true) {
str_starts_with($query, 'blog/new/') => 'blog-edit',
str_starts_with($query, 'blog/edit/') => 'blog-edit',
str_starts_with($query, 'blog/duplicate/') => 'blog-edit',
str_starts_with($query, 'blog') => 'blog-list',
default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}