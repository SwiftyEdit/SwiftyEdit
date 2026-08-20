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

        // ----- Pages -----

        // After the page has been rendered (tracking, recommendations, etc.)
        'page.display.after' => [
            'type' => 'generic',
            'context' => ['page_data', 'query'],
        ],

        // ----- Images -----

        // Requested by the {img} Smarty function (app/functions/functions.img.php)
        // for every tag call. An active image-processing plugin returns
        // ['src' => ..., 'srcset' => ...] to enable responsive variants;
        // returning the value unchanged (null) falls back to a plain <img src>.
        'image.variants' => [
            'type' => 'filter',
            'context' => ['src', 'widths', 'ratio', 'fit'],
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


    // ==========================
    // Global hooks
    // ==========================
    // Fire identically regardless of whether the triggering action happened
    // in the ACP (backend) or the frontend. Callbacks are loaded on both
    // request lifecycles (see plugins/<name>/hooks-global/).
    'global' => [

        // ----- Orders -----

        // An order has been marked as paid, either manually by an admin in the
        // ACP or automatically by a payment plugin (e.g. se_paypal-pay).
        'order.paid' => [
            'type'    => 'action',
            'context' => ['order_id', 'order', 'triggered_by'],
        ],
    ],
];
