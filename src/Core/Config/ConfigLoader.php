<?php

declare(strict_types=1);

namespace Folio\Core\Config;

final class ConfigLoader
{
    public function load(string $configPath): ConfigRepository
    {
        $items = [];

        foreach (glob(rtrim($configPath, '/'). '/*.php') ?: [] as $file) {
            $items[basename($file, '.php')] = require $file;
        }

        return new ConfigRepository($items);
    }
}
