<?php

$subinc = match (true) {
str_starts_with($query, 'blog/new/') => 'blog/blog-edit',
str_starts_with($query, 'blog/edit/') => 'blog/blog-edit',
str_starts_with($query, 'blog/duplicate/') => 'blog/blog-edit',
str_starts_with($query, 'blog') => 'blog/blog-list',
default => ''
};

if($subinc != '') {
    include $subinc.'.php';
}