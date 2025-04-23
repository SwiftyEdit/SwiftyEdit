<?php

$addon_image_src = SE_ROOT.'plugins/se_paypal-pay/poster.png';
$get_image = base64_encode(file_get_contents($addon_image_src));


echo '<div class="row">';
echo '<div class="col-md-9">';
echo '<div id="" hx-get="/admin/addons/plugin/se_paypal-pay/read/?show=paypal" hx-trigger="load">Loading data ...</div>';
echo '</div>';
echo '<div class="col-md-3">';

echo '<div class="card p-3">';
echo '<img src="data:image/png;base64,'.$get_image.'" class="img-fluid rounded">';
echo '</div>';


echo '</div>';
echo '</div>';
