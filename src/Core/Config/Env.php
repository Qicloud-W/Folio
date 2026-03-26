<?php

declare(strict_types=1);

namespace Folio\Core\Config;

final class Env
{
    private static array $loaded = [];

    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $trimmed, 2));
            $value = trim($value, "\"'");
            self::$loaded[$key] = $value;
            $_ENV[$key] = $value;
            putenv(sprintf('%s=%s', $key, $value));
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$loaded[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }
}
