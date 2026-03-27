<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Application\Application as RuntimeApplication;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\Foundation\Application as ApplicationContract;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Support\ServiceProvider;
use Throwable;

final class Application implements ApplicationContract
{
    private bool $bootstrapped = false;

    /** @var array<class-string, true> */
    private array $registeredProviders = [];

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
        $this->register(\Folio\Core\Providers\TranslationServiceProvider::class);
        $this->register(\Folio\Core\Providers\RoutingServiceProvider::class);
        $this->bootstrapped = true;

        return $this;
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

            return $existing;
        }

        $instance = is_string($provider) ? new $provider($this->container()) : $provider;
        $instance->register();
        $this->registeredProviders[$providerClass] = true;
        $this->container()->instance($providerClass, $instance);

        if (method_exists($instance, 'provides')) {
            foreach ((array) $instance->provides() as $abstract) {
                if (!$this->bound((string) $abstract)) {
                    continue;
                }

                $this->instance((string) $abstract, $this->make((string) $abstract));
            }
        }

        if (!$this->application->hasProvider($providerClass)) {
            $this->application->registerProvider($providerClass);
        }

        return $instance;
    }

    public function registered(string $provider): bool
    {
        return isset($this->registeredProviders[$provider]);
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

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->container()->make($abstract, $parameters);
    }

    public function container(): Container
    {
        return $this->application->container();
    }
}
