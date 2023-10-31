<?php

echo '<div class="subHeader">';
echo 'invoice.pay - readme';
echo '</div>';

$readme = file_get_contents(SE_CONTENT."/modules/se_invoice.pay/readme.md");

echo '<div class="card p-3">';
echo '<div class="scroll-container">';
$Parsedown = new Parsedown();
echo $Parsedown->text($readme);
echo '</div>';
echo '</div>';