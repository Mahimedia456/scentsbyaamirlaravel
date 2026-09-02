<?php

return [
    'url' => rtrim((string) env('WORDPRESS_URL', 'https://scentsbyaamir.com'), '/'),
    'timeout' => (int) env('WORDPRESS_TIMEOUT', 25),
    'per_page' => min(100, max(1, (int) env('WORDPRESS_PER_PAGE', 50))),
    'user_agent' => env('WORDPRESS_USER_AGENT', 'ScentsByAamir-Laravel-Journal-Importer/1.0'),
];
