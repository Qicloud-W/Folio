<?php

declare(strict_types=1);

namespace Folio\Core\Contracts\Support;

interface DeferrableProvider
{
    /** @return list<string> */
    public function provides(): array;
}
