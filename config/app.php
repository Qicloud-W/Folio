<?php

return [
    'name' => env('APP_NAME', 'Folio'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', false),
    'locale' => env('APP_LOCALE', 'zh-CN'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'zh-CN'),
    'middleware' => [
        \App\Http\Middleware\AlphaRequestTrace::class,
    ],
    'providers' => [
        \Folio\Core\Providers\TranslationServiceProvider::class,
    ],
];
