<?php
return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => 'users',
    ],
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users'
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\User::class,
        ],
    ],
];