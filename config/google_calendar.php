<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8080/google/calendar/callback'),
    'scopes' => [
        'https://www.googleapis.com/auth/calendar',
    ],
];
