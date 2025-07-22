<?php

echo '<h2>PayPal Plugin Docs</h2>';

echo '<div class="row">';
echo '<div class="col-md-3">';

echo '<div id="" hx-get="/admin-xhr-addons/plugin/se_paypal-pay/read/?show=docs_nav" hx-trigger="load">Loading data ...</div>';

echo '</div>';
echo '<div class="col-md-9">';

echo '<div id="docsContent" hx-get="/admin-xhr/addons/plugin/se_paypal-pay/read/?show=docs_content" hx-trigger="load">Loading data ...</div>';

echo '</div>';
echo '</div>';