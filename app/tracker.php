<?php

/**
 * track pageviews
 * @var array $page_contents app.php
 */

$hits_page_id = $page_contents['page_id'];
se_increase_pageimpression($hits_page_id);