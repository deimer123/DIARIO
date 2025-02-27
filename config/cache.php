<?php

use Illuminate\Support\Str;

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'laravel_cache'),
];