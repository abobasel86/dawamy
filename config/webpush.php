<?php
return [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:example@example.com'),
        'public_key'  => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
];
