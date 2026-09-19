<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'views' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'lowercase_usernames' => true,
    'limiters' => [
        'login' => 'login',
        'two-factor' => null,
        'verification' => '6,1',
        'password-reset' => '6,1',
    ],
    'paths' => [
        'login' => null,
        'logout' => null,
        'register' => null,
        'password' => [
            'request' => null,
            'reset' => null,
            'email' => null,
            'update' => null,
            'confirm' => null,
            'confirmation' => null,
        ],
        'verification' => [
            'notice' => null,
            'verify' => null,
            'send' => null,
        ],
        'user-profile-information' => ['update' => null],
        'user-password' => ['update' => null],
        'two-factor' => [
            'login' => null,
            'enable' => null,
            'confirm' => null,
            'disable' => null,
            'qr-code' => null,
            'secret-key' => null,
            'recovery-codes' => null,
        ],
    ],
    'redirects' => [
        'login' => '/dashboard',
        'logout' => '/',
        'register' => '/dashboard',
        'password-reset' => '/dashboard',
    ],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
    ],
];
