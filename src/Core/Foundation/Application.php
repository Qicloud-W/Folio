<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Application\Application as BaseApplication;

final class Application extends BaseApplication
{
    public static function configure(string $basePath): self
    {
        return new self(rtrim($basePath, '/'));
    }
}
