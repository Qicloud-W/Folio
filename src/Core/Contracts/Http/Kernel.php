<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Http;

use Folio\Core\Http\Request;
use Folio\Core\Http\Response;

interface Kernel
{
    public function handle(Request $request): Response;
}
