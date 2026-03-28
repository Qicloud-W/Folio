<?php

declare(strict_types=1);

namespace Folio\Core\Routing;

final class RouteDefinition
{
    public function __construct(
        private readonly Router $router,
        private readonly string $method,
        private readonly int $index,
    ) {
    }

    public function name(string $name): self
    {
        $this->router->setRouteName($this->method, $this->index, $name);

        return $this;
    }
}
