<?php

declare(strict_types=1);

namespace Folio\Core\Application;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Config\Env;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Http\MiddlewarePipeline;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Providers\AppServiceProvider;
use Folio\Core\Routing\Router;
use Throwable;

final class Application
{
    private readonly Container $container;

    private bool $bootstrapped = false;

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
        if ($this->bootstrapped) {
            return $this;
        }

        Env::load($this->basePath.'/.env');

        $config = (new ConfigLoader())->load($this->basePath.'/config');
        $this->container->instance(ConfigRepository::class, $config);
        $this->container->instance('config', $config);

        $this->registerProvider(AppServiceProvider::class);

        $providers = $config->get('app.providers', []);
        foreach (is_array($providers) ? $providers : [] as $providerClass) {
            if (is_string($providerClass)) {
                $this->registerProvider($providerClass);
            }
        }

        $this->registerProvider(\Folio\Core\Providers\RoutingServiceProvider::class);
        $this->bootstrapped = true;

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

        if ($this->bootstrapped) {
            $provider->boot();
        }

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
        $router = $this->container->make(Router::class);
        $config = $this->container->make(ConfigRepository::class);
        $middleware = $config->get('app.middleware', []);
        $pipeline = new MiddlewarePipeline(
            $this->container,
            is_array($middleware) ? $middleware : $this->middlewares,
        );

        try {
            return $pipeline->process(
                $request,
                static fn (Request $request): Response => $router->dispatch($request),
            );
        } catch (Throwable $exception) {
            /** @var ExceptionHandler $handler */
            $handler = $this->container->make(ExceptionHandler::class);
            $handler->report($exception);

            return $handler->render($request, $exception);
        }
    }

    public function container(): Container
    {
        return $this->container;
    }
}
