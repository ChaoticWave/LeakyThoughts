<?php
return [
    /*
    | Authentication Defaults
    */
    'defaults'  => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],
    /*
    | Authentication Guards
    */
    'guards'    => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver'   => 'token',
            'provider' => 'users',
        ],
    ],
    /*
    | User Providers
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => ChaoticWave\LeakyThoughts\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],
    /*
    | Resetting Passwords
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_resets',
            'expire'   => 60,
        ],
    ],
];
