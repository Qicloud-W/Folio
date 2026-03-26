<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Contracts\Foundation\Application;
use Folio\Core\Contracts\Http\Kernel as KernelContract;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Folio\Core\Pipeline\Pipeline;
use Folio\Core\Routing\Router;
use Throwable;

final class HttpKernel implements KernelContract
{
    public function __construct(private readonly Application $app)
    {
    }

    public function handle(Request $request): Response
    {
        try {
            $this->app->bootstrap();

            /** @var Router $router */
            $router = $this->app->make(Router::class);
            $middleware = $this->app->config('app.middleware', []);

            return (new Pipeline($this->app))
                ->send($request)
                ->through(is_array($middleware) ? $middleware : [])
                ->then(static fn (Request $request): Response => $router->dispatch($request));
        } catch (Throwable $exception) {
            /** @var ExceptionHandler $handler */
            $handler = $this->app->make(ExceptionHandler::class);
            $handler->report($exception);

            return $handler->render($request, $exception);
        }
    }
}
