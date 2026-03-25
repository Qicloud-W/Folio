<?php

declare(strict_types=1);

namespace Folio\Core;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\Env;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\I18n\Lang;
use Folio\Core\Routing\Router;
use Throwable;

final class Kernel
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function handle(Request $request): Response
    {
        try {
            Env::load($this->basePath.'/.env');
            $config = (new ConfigLoader())->load($this->basePath.'/config');
            $lang = new Lang($this->basePath.'/resources/lang');

            $router = new Router();
            $appName = (string) $config->get('app.name', 'Folio');
            $locale = (string) $config->get('app.locale', 'zh-CN');
            $debug = (bool) $config->get('app.debug', true);
            $pingMessage = $lang->get($locale, 'messages', 'pong', 'pong');

            $router->get('/health', static fn (): Response => Response::json([
                'status' => 'ok',
                'app' => $appName,
                'env' => (string) $config->get('app.env', 'local'),
            ]));

            $routeFile = $this->basePath.'/routes/api.php';
            if (is_file($routeFile)) {
                $registerRoutes = require $routeFile;
                if (is_callable($registerRoutes)) {
                    $registerRoutes($router, $locale, $pingMessage);
                }
            }

            return $router->dispatch($request);
        } catch (Throwable $exception) {
            return Response::json([
                'error' => [
                    'code' => 'INTERNAL_SERVER_ERROR',
                    'message' => $debug ?? false ? $exception->getMessage() : 'Internal Server Error',
                ],
            ], 500);
        }
    }
}
