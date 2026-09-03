<?php

return [
    'mode' => env('UBL_MODE', 'sandbox'),
    'base_url' => rtrim(env('UBL_BASE_URL', 'https://demo-ipg.ctdev.comtrust.ae:2443'), '/'),
    'public_url' => rtrim(env('UBL_PUBLIC_URL', env('APP_URL', 'https://shop.scentsbyaamir.com')), '/'),

    // Official UBL-linked EPG integration guide staging/demo values.
    // TEST ONLY. Replace all merchant values with UBL-issued production credentials before go-live.
    'customer' => env('UBL_CUSTOMER', 'Demo Merchant'),
    'store' => env('UBL_STORE', '0000'),
    'terminal' => env('UBL_TERMINAL', '0000'),
    'username' => env('UBL_USERNAME', 'Demo_fY9c'),
    'password' => env('UBL_PASSWORD', 'Comtrust@20182018'),

    'currency' => env('UBL_CURRENCY', 'PKR'),
    'channel' => env('UBL_CHANNEL', 'Web'),
    'transaction_hint' => env('UBL_TRANSACTION_HINT', 'CPT:Y;VCC:Y;'),
    'order_name' => env('UBL_ORDER_NAME', 'Scents by Aamir'),

    'timeout' => (int) env('UBL_TIMEOUT', 30),
    'connect_timeout' => (int) env('UBL_CONNECT_TIMEOUT', 12),
    'verify_ssl' => filter_var(env('UBL_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
];
