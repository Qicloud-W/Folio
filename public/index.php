<?php

declare(strict_types=1);

use Folio\Core\Http\Request;
use Folio\Core\Kernel;

require_once dirname(__DIR__).'/src/Core/Support/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Folio\\' => dirname(__DIR__).'/src/',
        'App\\' => dirname(__DIR__).'/app/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $path = $baseDir.str_replace('\\', '/', $relative).'.php';

        if (is_file($path)) {
            require_once $path;
        }
    }
});

$request = Request::capture();
$response = (new Kernel(dirname(__DIR__)))->handle($request);
$response->send();
