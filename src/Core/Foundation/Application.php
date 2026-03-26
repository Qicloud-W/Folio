<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Config\ConfigLoader;
use Folio\Core\Config\Env;
use Folio\Core\Container\Container;
use Folio\Core\Contracts\Container\Container as ContainerContract;
use Folio\Core\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Folio\Core\Contracts\Foundation\Application as ApplicationContract;
use Folio\Core\Contracts\Http\Kernel as HttpKernelContract;
use Folio\Core\Contracts\Support\DeferrableProvider;
use Folio\Core\Exceptions\Handler;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Providers\RoutingServiceProvider;
use Folio\Core\Providers\TranslationServiceProvider;
use Folio\Core\Support\ServiceProvider;

final class Application extends Container implements ApplicationContract
{
    private bool $bootstrapped = false;

    /** @var array<int, ServiceProvider> */
    private array $serviceProviders = [];

    /** @var array<class-string<ServiceProvider>, bool> */
    private array $loadedProviders = [];

    /** @var array<string, class-string<ServiceProvider>> */
    private array $deferredServices = [];

    public function __construct(private readonly string $basePath)
    {
        parent::__construct();

        $this->instance(ContainerContract::class, $this);
        $this->instance(self::class, $this);
        $this->instance(ApplicationContract::class, $this);
        $this->instance('app', $this);
        $this->instance('path.base', $this->basePath);

        $this->singleton(ExceptionHandlerContract::class, Handler::class);
        $this->singleton(HttpKernelContract::class, HttpKernel::class);
    }

    public static function configure(string $basePath): self
    {
        return new self(rtrim($basePath, '/'));
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

        Env::load($this->basePath('.env'));
        $this->instance('config', (new ConfigLoader())->load($this->basePath('config')));

        $this->registerBaseServiceProviders();
        $this->registerConfiguredProviders();
        $this->bootServiceProviders();

        $this->bootstrapped = true;

        return $this;
    }

    public function handle(Request $request): Response
    {
        return $this->make(HttpKernelContract::class)->handle($request);
    }

    public function register(ServiceProvider|string $provider): ServiceProvider
    {
        $instance = is_string($provider) ? new $provider($this) : $provider;
        $providerClass = $instance::class;

        if (isset($this->loadedProviders[$providerClass])) {
            return $instance;
        }

        if ($instance instanceof DeferrableProvider) {
            foreach ($instance->provides() as $service) {
                $this->deferredServices[$service] = $providerClass;
            }
        }

        $instance->register();

        $this->serviceProviders[] = $instance;
        $this->loadedProviders[$providerClass] = true;

        if ($this->bootstrapped) {
            $instance->boot();
        }

        return $instance;
    }

    public function registered(string $provider): bool
    {
        return isset($this->loadedProviders[$provider]);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $config = $this->make('config');

        return $config->get($key, $default);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        $this->loadDeferredProviderIfNeeded($abstract);

        return parent::make($abstract, $parameters);
    }

    private function registerBaseServiceProviders(): void
    {
        $this->register(RoutingServiceProvider::class);
        $this->register(TranslationServiceProvider::class);
    }

    private function registerConfiguredProviders(): void
    {
        $providers = $this->config('app.providers', []);

        if (!is_array($providers)) {
            return;
        }

        foreach ($providers as $provider) {
            if (is_string($provider) && $provider !== '') {
                $this->register($provider);
            }
        }
    }

    private function bootServiceProviders(): void
    {
        foreach ($this->serviceProviders as $provider) {
            $provider->boot();
        }
    }

    private function loadDeferredProviderIfNeeded(string $abstract): void
    {
        $provider = $this->deferredServices[$abstract] ?? null;

        if ($provider === null || $this->registered($provider)) {
            return;
        }

        unset($this->deferredServices[$abstract]);
        $this->register($provider);
    }
}
