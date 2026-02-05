<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bot path blacklist
    |--------------------------------------------------------------------------
    |
    | These paths are routinely hit by Shopify/WordPress scanners and should
    | not be allowed to redirect to the public holding page. Paths listed in
    | `exact_paths` must match exactly once normalised (leading slash added).
    | Entries in `path_prefixes` block any request whose normalised path starts
    | with the given prefix. Update this list as new nuisance routes appear.
    */
    'exact_paths' => [
        '/sw.js',
        '/sw',
    ],

    'path_prefixes' => [
        '/cdn/shop',
        '/cdn/files',
        '/wp-admin',
        '/wp-content',
        '/wp-includes',
        '/wp-json',
        '/xmlrpc.php',
    ],
];
