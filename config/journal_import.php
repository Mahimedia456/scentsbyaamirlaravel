<?php

return [
    'wordpress_url' => env('WORDPRESS_JOURNAL_URL', 'https://scentsbyaamir.com'),
    'per_page' => (int) env('WORDPRESS_JOURNAL_PER_PAGE', 20),
    'timeout' => (int) env('WORDPRESS_JOURNAL_TIMEOUT', 30),
    'storage_disk' => env('WORDPRESS_JOURNAL_DISK', 'public'),
    'storage_directory' => trim(env('WORDPRESS_JOURNAL_DIRECTORY', 'journal'), '/'),
];
