<?php

return [
    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'Settings',
            'sort' => 2,
            'icon' => 'heroicon-o-key',
            'should_register_navigation' => true,
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
    'route' => [
        'panel_prefix' => false,
        'use_resource_middlewares' => false,
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ],
    'login-rules' => [
        'email' => 'required|email',
        'password' => 'required',
    ],
    'login-middleware' => [

    ],
    'logout-middleware' => [
        'auth:sanctum',
    ],
    'use-spatie-permission-middleware' => true,
];
