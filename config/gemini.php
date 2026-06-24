<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    | GEMINI_API_KEY in .env is the dev shortcut.
    | Production: store encrypted in `settings` table via Admin → Settings.
    |--------------------------------------------------------------------------
    */
    'api_key' => env('GEMINI_API_KEY'),
    'model'   => env('GEMINI_MODEL', 'gemini-1.5-flash'),
];
