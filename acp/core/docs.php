<?php

/**
 * show support offcanvas
 * check if is installed/activated support.mod
 * if not, show open source docs
 */

echo '<div class="card mb-3">';
echo '<iframe style="min-height: 60vh;" class="w-100" src="core/docs-viewer.php"></iframe>';
echo '</div>';


echo '<div class="card">';
echo '<div class="card-body">';

echo $lang['msg_community_edition'];

echo '<ul>';
echo '<li><a href="https://SwiftyEdit.org" title="" target="_blank">SwiftyEdit.org</a></li>';
echo '<li><a href="https://github.com/SwiftyEdit/" title="" target="_blank">GitHub.com</a></li>';
echo '</ul>';

echo '</div>';
echo '</div>';