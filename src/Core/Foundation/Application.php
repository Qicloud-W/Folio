<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Application\Application as RuntimeApplication;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\Foundation\Application as ApplicationContract;
use Folio\Core\Contracts\Http\Middleware as MiddlewareContract;
use Folio\Core\Contracts\ServiceProvider;
use Folio\Core\Contracts\Support\DeferrableProvider;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Application implements ApplicationContract
{
    private bool $bootstrapped = false;

    /** @var array<class-string, true> */
    private array $registeredProviders = [];

    /** @var array<class-string, true> */
    private array $bootedProviders = [];

    private function __construct(
        private readonly string $basePath,
        private readonly RuntimeApplication $application,
    ) {
    }

    public static function configure(string $basePath): self
    {
        $basePath = rtrim($basePath, '/');

        return new self($basePath, new RuntimeApplication($basePath));
    }

    public function basePath(string $path = ''): string
    {
        if ($path === '') {
            return $this->basePath;
        }

        return $this->basePath.'/'.ltrim($path, '/');
    }

    public function bootstrap(): self
    {
        if ($this->bootstrapped) {
            return $this;
        }

        $this->application->bootstrap();
        $this->bootstrapped = true;

        return $this;
    }

    public function withMiddleware(array $middlewares): self
    {
        $this->application->withMiddleware($middlewares);

        return $this;
    }

    public function prependMiddleware(string|MiddlewareContract|callable $middleware): self
    {
        $this->application->prependMiddleware($middleware);

        return $this;
    }

    public function appendMiddleware(string|MiddlewareContract|callable $middleware): self
    {
        $this->application->appendMiddleware($middleware);

        return $this;
    }

    public function middleware(): array
    {
        return $this->application->middleware();
    }

    public function handle(Request $request): Response
    {
        try {
            return $this->bootstrap()->application->handle($request);
        } catch (Throwable $exception) {
            return $this->report($exception);
        }
    }

    public function report(Throwable $exception): Response
    {
        /** @var ExceptionHandler $handler */
        $handler = $this->make(ExceptionHandler::class);
        $handler->report($exception);

        return $handler->render(Request::capture(), $exception);
    }

    public function register(ServiceProvider|string $provider): ServiceProvider
    {
        $providerClass = is_string($provider) ? $provider : $provider::class;

        if (isset($this->registeredProviders[$providerClass])) {
            /** @var ServiceProvider $existing */
            $existing = $this->make($providerClass);
            $this->bootProviderIfNeeded($existing);

            return $existing;
        }

        $instance = is_string($provider) ? new $provider($this->container()) : $provider;
        $this->registeredProviders[$providerClass] = true;
        $this->container()->instance($providerClass, $instance);

        if (!$instance instanceof DeferrableProvider) {
            $instance->register();
        }

        if (!$this->application->hasProvider($providerClass)) {
            $this->application->registerProvider($providerClass);
        }

        $this->bootProviderIfNeeded($instance);

        return $instance;
    }

    public function registered(string $provider): bool
    {
        return isset($this->registeredProviders[$provider]) || $this->application->hasProvider($provider);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->make(ConfigRepository::class)->get($key, $default);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $this->container()->bind($abstract, $concrete, $shared);
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->container()->singleton($abstract, $concrete);
    }

    public function instance(string $abstract, mixed $instance): mixed
    {
        return $this->container()->instance($abstract, $instance);
    }

    public function bound(string $abstract): bool
    {
        return $this->container()->bound($abstract);
    }

    public function has(string $abstract): bool
    {
        return $this->container()->has($abstract);
    }

    public function set(string $abstract, mixed $instance): mixed
    {
        return $this->container()->set($abstract, $instance);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->application->make($abstract, $parameters);
    }

    public function container(): Container
    {
        return $this->application->container();
    }

    private function bootProviderIfNeeded(ServiceProvider $provider): void
    {
        $providerClass = $provider::class;

        if (!$this->bootstrapped || isset($this->bootedProviders[$providerClass]) || $provider instanceof DeferrableProvider) {
            return;
        }

        $provider->boot();
        $this->bootedProviders[$providerClass] = true;
    }
}
