<?php

declare(strict_types=1);

namespace Folio\Core\Providers;

use Folio\Core\Http\Response;
use Folio\Core\Routing\Router;
use Folio\Core\Support\ServiceProvider;

final class RoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Router::class, function () {
            $router = new Router();
            $appName = (string) $this->app->config('app.name', 'Folio');
            $locale = (string) $this->app->config('app.locale', 'zh-CN');
            $translator = $this->app->make('translator');
            $pingMessage = $translator->get($locale, 'messages', 'pong', 'pong');

            $router->get('/health', static fn (): Response => Response::json([
                'status' => 'ok',
                'app' => $appName,
                'env' => (string) $this->app->config('app.env', 'local'),
            ]));

            $routeFile = $this->app->basePath('routes/api.php');
            if (is_file($routeFile)) {
                $registerRoutes = require $routeFile;
                if (is_callable($registerRoutes)) {
                    $registerRoutes($router, $locale, $pingMessage);
                }
            }

            return $router;
        });
    }
}
