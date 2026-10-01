<?php

// app/api/scopes.php

// Official SwiftyEdit API scope registry
// This file defines every scope an API key can be granted. It drives both
// the checkboxes in the ACP (Settings > API keys) and the scope check in
// app/handlers/api-routes.php - scope strings must not be used anywhere
// else without being listed here.
//
// Only add a scope once its endpoint exists, so the ACP never offers
// access to something the API can't actually serve.
//
// 'label' is a key from languages/en/backend.json (dots, not underscores)

return [

    'products:read' => [
        'label' => 'api_keys.scope.products_read',
    ],

    // additional permission, checked inside the products endpoint: prices
    // are returned even if the shop only shows them to logged-in customers
    'products:prices' => [
        'label' => 'api_keys.scope.products_prices',
    ],

    'posts:read' => [
        'label' => 'api_keys.scope.posts_read',
    ],

    'events:read' => [
        'label' => 'api_keys.scope.events_read',
    ],

];
