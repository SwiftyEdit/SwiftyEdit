<?php


// app/hooks-map.php

// Official SwiftyEdit hooks registry
// This file defines all stable hook names and their context.

return [

    // ==========================
    // Frontend hooks
    // ==========================
    'frontend' => [

        // ----- Products -----

        // Before a single product is rendered (data can be modified via filter)
        'product.display.before' => [
            'type' => 'filter',      // value in, value out
            'context' => ['product_id', 'product'],
        ],

        // Action area (buttons etc.) inside product view
        'product.display.actions' => [
            'type' => 'filter',
            'context' => ['product_id', 'product'],
        ],

        // After the product has been rendered (tracking, recommendations, etc.)
        'product.display.after' => [
            'type' => 'action',
            'context' => ['product_id', 'product'],
        ],

    ],


    // ==========================
    // Backend hooks
    // ==========================
    'backend' => [

        // ----- Pages -----

        'page.updated' => [
            'type'    => 'action',
            'context' => ['page_id', 'data', 'changes', 'user_id'],
        ],

        // ----- Products -----

        // A product has been updated in the backend
        'product.updated' => [
            'type' => 'action',
            'context' => ['product_id', 'data', 'changes', 'user_id'],
        ],

        // A product has been deleted in the backend
        'product.deleted' => [
            'type' => 'action',
            'context' => ['product_id', 'user_id'],
        ],


        // ----- Users -----

        'user.created' => [
            'type' => 'action',
            'context' => ['user_id', 'data', 'created_by'],
        ],

        'user.updated' => [
            'type' => 'action',
            'context' => ['user_id', 'data', 'changes', 'updated_by'],
        ],

        'user.deleted' => [
            'type' => 'action',
            'context' => ['user_id', 'deleted_by'],
        ],
    ],
];
