<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Application\Application as BaseApplication;
use Folio\Core\Config\ConfigRepository;
use Folio\Core\Contracts\Foundation\Application as ApplicationContract;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Support\ServiceProvider;

final class Application implements ApplicationContract
{
    private bool $bootstrapped = false;

    /** @var array<class-string, true> */
    private array $registeredProviders = [];

    private function __construct(
        private readonly string $basePath,
        private readonly BaseApplication $application,
    ) {
    }

    public static function configure(string $basePath): self
    {
        $basePath = rtrim($basePath, '/');

        return new self($basePath, new BaseApplication($basePath));
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
        if (!$this->bootstrapped) {
            $this->application->bootstrap();
            $this->bootstrapped = true;
            $this->registeredProviders[\Folio\Core\Providers\TranslationServiceProvider::class] = true;
        }

        return $this;
    }

    public function handle(Request $request): Response
    {
        return $this->application->handle($request);
    }

    public function register(ServiceProvider|string $provider): ServiceProvider
    {
        $providerClass = is_string($provider) ? $provider : $provider::class;
        $instance = is_string($provider) ? new $provider($this) : $provider;

        $instance->register();
        $this->registeredProviders[$providerClass] = true;

        if (method_exists($instance, 'provides')) {
            foreach ((array) $instance->provides() as $abstract) {
                if ($this->bound($abstract)) {
                    continue;
                }

                $this->instance((string) $abstract, $this->make((string) $abstract));
            }
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
        $this->application->container()->bind($abstract, $concrete, $shared);
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->application->container()->singleton($abstract, $concrete);
    }

    public function instance(string $abstract, mixed $instance): mixed
    {
        return $this->application->container()->instance($abstract, $instance);
    }

    public function bound(string $abstract): bool
    {
        return $this->application->container()->bound($abstract);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->application->container()->make($abstract, $parameters);
    }
}