<?php

return [
    // Model & resource references
    'navigation_model' => \Digitonic\FilamentNavigation\Models\Navigation::class,
    'navigation_resource' => \App\Filament\Resources\NavigationResource::class,

    // Default menu structure: keep only essential admin links
    'menus' => [
        'admin' => [
            [
                'label' => 'Dashboard',
                'url' => '/',
                'icon' => 'heroicon-o-home',
            ],
            [
                'label' => 'Programs',
                'route' => 'filament.admin.resources.programs.index',
                'icon' => 'heroicon-o-rectangle-stack',
            ],
            [
                'label' => 'Users',
                'route' => 'filament.admin.resources.users.index',
                'icon' => 'heroicon-o-users',
            ],
        ],
    ],
];
