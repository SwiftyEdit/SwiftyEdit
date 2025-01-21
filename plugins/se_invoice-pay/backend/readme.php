<?php

$readme = file_get_contents($this_addon_root."/readme.md");

echo '<div class="card p-3">';
echo '<div class="scroll-container">';
$Parsedown = new Parsedown();
echo $Parsedown->text($readme);
echo '</div>';
echo '</div>';