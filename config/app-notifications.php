<?php

return [
    // Delivery strategy for app-triggered notifications.
    // - 'immediate': write directly to database (no queue worker required)
    // - 'queued'   : dispatch to queue according to QUEUE_CONNECTION
    'delivery' => env('APP_NOTIFICATIONS_DELIVERY', 'immediate'),

    // Recipients config for specific events
    'recipients' => [
        // When a student requests enrollment, notify these permissions (Gate-based)
        // You can override via .env as comma-separated list if needed
        'enrollment_requested_permissions' => explode(',', env('APP_NOTIFY_ENROLLMENT_PERMS', 'receive_program_enrollment_request')),

        // Optional fallback roles to include (e.g., super_admin which may bypass gate via Gate::before)
        'enrollment_requested_roles' => explode(',', env('APP_NOTIFY_ENROLLMENT_ROLES', 'super_admin')),
    ],
];
