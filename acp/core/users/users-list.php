<?php

echo '<div class="subHeader d-flex align-items-center">';
echo $icon['users'].' '.$lang['nav_btn_user'];
echo '<a href="/admin/snippets/new/" class="btn btn-default text-success ms-auto">'.$icon['plus'].' '.$lang['new'].'</a>';
echo '</div>';

$reader_uri = '/admin/user/read/';
$writer_uri = '/admin/user/write/';