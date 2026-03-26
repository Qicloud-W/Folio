<?php

declare(strict_types=1);

use Folio\Core\Config\Env;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = Env::get($key, $default);

        return match ($value) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            default => $value,
        };
    }
}
