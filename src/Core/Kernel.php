<?php

declare(strict_types=1);

namespace Folio\Core;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\ConfigRepository;
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
        $debug = false;

        try {
            Env::load($this->basePath.'/.env');
            $config = (new ConfigLoader())->load($this->basePath.'/config');
            $debug = (bool) $config->get('app.debug', false);
            $lang = new Lang($this->basePath.'/resources/lang');

            $router = new Router();
            $appName = (string) $config->get('app.name', 'Folio');
            $locale = (string) $config->get('app.locale', 'zh-CN');
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
            return $this->renderThrowable($exception, $debug);
        }
    }

    public function report(Throwable $exception, bool $debug = false): Response
    {
        return $this->renderThrowable($exception, $debug);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        static $config = null;

        if (!$config instanceof ConfigRepository) {
            Env::load($this->basePath.'/.env');
            $config = (new ConfigLoader())->load($this->basePath.'/config');
        }

        return $config->get($key, $default);
    }

    private function renderThrowable(Throwable $exception, bool $debug): Response
    {
        return Response::safeJson([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $debug ? $exception->getMessage() : 'Internal Server Error',
            ],
        ], 500);
    }
}
