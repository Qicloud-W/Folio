<?php

declare(strict_types=1);

namespace Folio\Core\Foundation;

use Folio\Core\Contracts\Foundation\Application;
use Folio\Core\Contracts\Http\Kernel as KernelContract;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

final class HttpKernel implements KernelContract
{
    public function __construct(private readonly Application $app)
    {
    }

    public function handle(Request $request): Response
    {
        return $this->app->handle($request);
    }
}
