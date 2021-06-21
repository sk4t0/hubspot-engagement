<?php
// config/hubspot.php

return [
    'api_key' => env('HUBSPOT_API_KEY'),
    /*
     * Guzzle options
     */
    'CLIENT_OPTIONS' => [
        'HTTP_ERRORS' => true,
    ]
];
