<?php

return [
    // Permission slugs used to determine which dashboard variant a user sees.
    // Grant one of these to roles/users; code will read from here.
    'permissions' => [
        'super_admin' => 'dashboard.view.super_admin',
        'admin' => 'dashboard.view.admin',
        'pengajar' => 'dashboard.view.pengajar',
        'pelajar' => 'dashboard.view.pelajar',
    ],

    // Cache lifetime for stats blocks (in seconds)
    'stats_cache_seconds' => 60,

    // Trend and sparkline controls
    'trend' => [
        // Number of days in the comparison window (e.g. 7 means last 7 days vs previous 7 days)
        'window_days' => 7,
        // Number of days included in the sparkline (e.g. 7 shows last 7 days including today)
        'spark_days' => 7,
    ],

    // Optional: fallback to role-name detection for backwards compatibility
    'role_fallback' => [
        'enabled' => true,
        'aliases' => [
            'super_admin' => ['super admin', 'super_admin', 'owner'],
            'admin' => ['admin', 'administrator'],
            'pengajar' => ['pengajar', 'teacher', 'dosen', 'instructor'],
            'pelajar' => ['pelajar', 'student', 'mahasiswa', 'learner'],
        ],
    ],
];

