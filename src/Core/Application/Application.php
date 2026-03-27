<?php

declare(strict_types=1);

namespace Folio\Core\Application;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Config\Env;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\Http\Middleware as MiddlewareContract;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Contracts\Support\DeferrableProvider;
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

    /** @var array<string, class-string<ServiceProvider>> */
    private array $deferredProviders = [];

    /** @var array<class-string<ServiceProvider>, bool> */
    private array $bootedProviders = [];

    /** @var list<class-string<MiddlewareContract>|MiddlewareContract|callable> */
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
        $this->bootRegisteredProviders();

        return $this;
    }

    public function registerProvider(string $providerClass): self
    {
        if ($this->hasProvider($providerClass)) {
            $this->bootProviderIfNeeded($providerClass);

            return $this;
        }

        /** @var ServiceProvider $provider */
        $provider = new $providerClass($this->container);

        if ($provider instanceof DeferrableProvider) {
            foreach ($provider->provides() as $abstract) {
                $this->deferredProviders[$abstract] = $providerClass;
            }
            $this->providers[$providerClass] = $provider;

            return $this;
        }

        $provider->register();
        $this->providers[$providerClass] = $provider;
        $this->bootProviderIfNeeded($providerClass);

        return $this;
    }

    public function hasProvider(string $providerClass): bool
    {
        return isset($this->providers[$providerClass]);
    }

    public function provider(string $providerClass): ?ServiceProvider
    {
        return $this->providers[$providerClass] ?? null;
    }

    public function isProviderBooted(string $providerClass): bool
    {
        return isset($this->bootedProviders[$providerClass]);
    }

    public function isDeferredService(string $abstract): bool
    {
        return isset($this->deferredProviders[$abstract]);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        $this->loadDeferredProvider($abstract);

        return $this->container->make($abstract, $parameters);
    }

    /** @param list<class-string<MiddlewareContract>|MiddlewareContract|callable> $middlewares */
    public function withMiddleware(array $middlewares): self
    {
        $this->middlewares = array_values($middlewares);

        return $this;
    }

    /** @return list<class-string<MiddlewareContract>|MiddlewareContract|callable> */
    public function middleware(): array
    {
        return $this->middlewares;
    }

    public function appendMiddleware(string|MiddlewareContract|callable $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function prependMiddleware(string|MiddlewareContract|callable $middleware): self
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }

    public function handle(Request $request): Response
    {
        $router = $this->make(Router::class);
        $config = $this->make(ConfigRepository::class);
        $configured = $config->get('app.middleware', []);
        $pipeline = new MiddlewarePipeline(
            $this->container,
            [
                ...(is_array($configured) ? array_values($configured) : []),
                ...$this->middlewares,
            ],
        );

        try {
            return $pipeline->process(
                $request,
                static fn (Request $request): Response => $router->dispatch($request),
            );
        } catch (Throwable $exception) {
            /** @var ExceptionHandler $handler */
            $handler = $this->make(ExceptionHandler::class);
            ob_start();
            try {
                $handler->report($exception);
            } finally {
                ob_end_clean();
            }

            return $handler->render($request, $exception);
        }
    }

    public function container(): Container
    {
        return $this->container;
    }

    private function bootRegisteredProviders(): void
    {
        foreach (array_keys($this->providers) as $providerClass) {
            $this->bootProviderIfNeeded($providerClass);
        }
    }

    private function bootProviderIfNeeded(string $providerClass): void
    {
        if (!$this->bootstrapped || isset($this->bootedProviders[$providerClass])) {
            return;
        }

        $provider = $this->providers[$providerClass] ?? null;
        if ($provider === null || $provider instanceof DeferrableProvider) {
            return;
        }

        $provider->boot();
        $this->bootedProviders[$providerClass] = true;
    }

    private function loadDeferredProvider(string $abstract): void
    {
        $providerClass = $this->deferredProviders[$abstract] ?? null;
        if ($providerClass === null) {
            return;
        }

        /** @var ServiceProvider&DeferrableProvider $provider */
        $provider = $this->providers[$providerClass];
        $provider->register();

        foreach ($provider->provides() as $provided) {
            unset($this->deferredProviders[$provided]);
        }

        $this->bootProviderIfNeeded($providerClass);
    }
}
