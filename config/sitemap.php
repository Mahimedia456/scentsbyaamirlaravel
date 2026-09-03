<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public canonical base URL
    |--------------------------------------------------------------------------
    |
    | Sitemap URLs must point to the public production storefront even when
    | the sitemap is inspected locally.
    |
    */
    'base_url' => rtrim(env('SITEMAP_BASE_URL', 'https://scentsbyaamir.com'), '/'),
];
