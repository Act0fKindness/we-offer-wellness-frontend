<?php

return [
    // Feature flag to enable/disable static marketing/content pages
    // When false, those routes won't be registered; fallback handles pages.
    'enable_static_pages' => env('ENABLE_STATIC_PAGES', false),
];

