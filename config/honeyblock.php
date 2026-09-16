<?php

declare(strict_types=1);

return [

    'all_404' => false,

    'traps' => [
        'admin',
        'administrator',
        'wp-admin',
        'wp-login.php',
        'phpmyadmin',
        'dbadmin',
        'manager',
    ],

    // Timeout added to trapped request ip
    'base_timeout' => 1,

    // Timeout multiplier after each trapped request
    'timeout_multiplier' => 2,

    // time before a honeypot request is forgotten. 0 is never. default 1 hour
    'decay' => 1 * 60 * 60,
];
