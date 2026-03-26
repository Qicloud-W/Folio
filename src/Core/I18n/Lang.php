<?php

declare(strict_types=1);

namespace Folio\Core\I18n;

final class Lang
{
    public function __construct(private readonly string $langPath)
    {
    }

    public function get(string $locale, string $group, string $key, string $default = ''): string
    {
        $file = rtrim($this->langPath, '/').'/'.$locale.'/'.$group.'.php';

        if (!is_file($file)) {
            return $default;
        }

        $lines = require $file;

        return $lines[$key] ?? $default;
    }
}
