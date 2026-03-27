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
        $this->container->singleton(Router::class, function (\Folio\Core\Container\Container $container) {
            $router = new Router();
            $config = $container->make('config');
            $appName = (string) $config->get('app.name', 'Folio');
            $locale = (string) $config->get('app.locale', 'zh-CN');
            $translator = $container->bound('translator')
                ? $container->make('translator')
                : new \Folio\Core\I18n\Lang($container->make('basePath').'/resources/lang');
            $pingMessage = $translator->get($locale, 'messages', 'pong', 'pong');

            $router->get('/health', static fn (): Response => Response::json([
                'status' => 'ok',
                'app' => $appName,
                'env' => (string) $config->get('app.env', 'local'),
            ]));

            $routeFile = $container->make('basePath').'/routes/api.php';
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
