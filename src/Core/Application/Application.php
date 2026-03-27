<?php

declare(strict_types=1);

namespace Folio\Core\Application;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Config\Env;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Exceptions\HttpException;
use Folio\Core\Http\MiddlewarePipeline;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\I18n\Lang;
use Folio\Core\Providers\AppServiceProvider;
use Folio\Core\Routing\Router;
use Throwable;

final class Application
{
    private readonly Container $container;

    /** @var array<class-string<ServiceProvider>, ServiceProvider> */
    private array $providers = [];

    /** @var list<class-string|object|callable> */
    private array $middlewares = [];

    public function __construct(private readonly string $basePath)
    {
        $this->container = new Container();
        $this->container->set('basePath', $this->basePath);
        $this->container->set(Container::class, $this->container);
    }

    public function bootstrap(): self
    {
        Env::load($this->basePath.'/.env');

        $config = (new ConfigLoader())->load($this->basePath.'/config');
        $this->container->set(ConfigRepository::class, $config);
        $this->container->set(Router::class, new Router());

        $this->registerProvider(AppServiceProvider::class);

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        return $this;
    }

    public function registerProvider(string $providerClass): self
    {
        if ($this->hasProvider($providerClass)) {
            return $this;
        }

        /** @var ServiceProvider $provider */
        $provider = new $providerClass($this->container);
        $provider->register();
        $this->providers[$providerClass] = $provider;

        return $this;
    }

    public function hasProvider(string $providerClass): bool
    {
        return isset($this->providers[$providerClass]);
    }

    public function withMiddleware(array $middlewares): self
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    public function handle(Request $request): Response
    {
        try {
            $router = $this->container->make(Router::class);
            $config = $this->container->make(ConfigRepository::class);
            $lang = $this->container->make(Lang::class);

            $router->get('/health', static fn (): Response => Response::json([
                'status' => 'ok',
                'app' => (string) $config->get('app.name', 'Folio'),
                'env' => (string) $config->get('app.env', 'local'),
            ]));

            $routeFile = $this->basePath.'/routes/api.php';
            if (is_file($routeFile)) {
                $registerRoutes = require $routeFile;
                if (is_callable($registerRoutes)) {
                    $registerRoutes(
                        $router,
                        (string) $config->get('app.locale', 'zh-CN'),
                        $lang->get((string) $config->get('app.locale', 'zh-CN'), 'messages', 'pong', 'pong')
                    );
                }
            }

            $pipeline = new MiddlewarePipeline($this->container, $this->middlewares);

            return $pipeline->process(
                $request,
                static fn (Request $request): Response => $router->dispatch($request),
            );
        } catch (Throwable $exception) {
            return $this->renderThrowable($exception);
        }
    }

    public function container(): Container
    {
        return $this->container;
    }

    private function renderThrowable(Throwable $exception): Response
    {
        $debug = (bool) $this->container->make(ConfigRepository::class)->get('app.debug', false);

        if ($exception instanceof HttpException) {
            return Response::safeJson([
                'error' => array_filter([
                    'code' => $exception->errorCode(),
                    'message' => $exception->status() >= 500 && !$debug ? 'Internal Server Error' : $exception->getMessage(),
                    ...$exception->meta(),
                ], static fn (mixed $value): bool => $value !== null),
            ], $exception->status(), $exception->status() === 405 && isset($exception->meta()['allowed_methods'])
                ? ['Allow' => implode(', ', $exception->meta()['allowed_methods'])]
                : []);
        }

        return Response::safeJson([
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $debug ? $exception->getMessage() : 'Internal Server Error',
            ],
        ], 500);
    }
}
