<?php

return [
    'labels' => [
        'super_admin' => 'Super Admin',
        'admin' => 'Administrator',
        'manager' => 'Store Manager',
        'catalog_manager' => 'Catalog Manager',
        'order_manager' => 'Order Manager',
        'content_manager' => 'Content Manager',
        'staff' => 'Staff',
    ],

    'permissions' => [
        'super_admin' => ['*'],
        'admin' => ['dashboard','catalog','orders','customers','inventory','promotions','content','media','analytics','support'],
        'manager' => ['dashboard','catalog','orders','customers','inventory','promotions','content','media','analytics','support'],
        'catalog_manager' => ['dashboard','catalog','inventory','promotions','media'],
        'order_manager' => ['dashboard','orders','customers','inventory','analytics','support'],
        'content_manager' => ['dashboard','content','media','analytics'],
        'staff' => ['dashboard'],
    ],
];
