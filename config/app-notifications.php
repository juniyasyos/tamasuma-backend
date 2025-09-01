<?php

return [
    // Delivery strategy for app-triggered notifications.
    // - 'immediate': write directly to database (no queue worker required)
    // - 'queued'   : dispatch to queue according to QUEUE_CONNECTION
    'delivery' => env('APP_NOTIFICATIONS_DELIVERY', 'immediate'),
];

