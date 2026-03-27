<?php

declare(strict_types=1);

namespace Folio\Core\Exceptions;

use Folio\Core\Config\ConfigRepository;
use Folio\Core\Contracts\Debug\ExceptionHandler;
use Folio\Core\Http\Request;
use Folio\Core\Http\Response;
use Throwable;

final class Handler implements ExceptionHandler
{
    public function __construct(private readonly ConfigRepository $config)
    {
    }

    public function report(Throwable $exception): void
    {
        error_log($exception->__toString());
    }

    public function render(Request $request, Throwable $exception): Response
    {
        return Response::safeJson([
            'error' => $this->payload($request, $exception),
        ], $this->status($exception), $this->headers($exception));
    }

    private function payload(Request $request, Throwable $exception): array
    {
        $payload = [
            'code' => $this->code($exception),
            'message' => $this->message($exception),
        ];

        if ($exception instanceof MethodNotAllowedHttpException) {
            $payload['allowed_methods'] = $exception->allowedMethods();
        }

        if ($exception instanceof HttpException) {
            $payload = [...$payload, ...$exception->meta()];
        }

        if ($this->debug()) {
            $payload['debug'] = [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'path' => $request->path(),
                'method' => $request->method(),
            ];
        }

        return array_filter($payload, static fn (mixed $value): bool => $value !== null);
    }

    private function status(Throwable $exception): int
    {
        return $exception instanceof HttpException ? $exception->status() : 500;
    }

    private function headers(Throwable $exception): array
    {
        return $exception instanceof HttpException ? $exception->headers() : [];
    }

    private function code(Throwable $exception): string
    {
        return $exception instanceof HttpException ? $exception->errorCode() : 'INTERNAL_SERVER_ERROR';
    }

    private function message(Throwable $exception): string
    {
        if (!$this->shouldSanitize($exception)) {
            return $exception->getMessage();
        }

        return 'Internal Server Error';
    }

    private function shouldSanitize(Throwable $exception): bool
    {
        return $this->status($exception) >= 500 && !$this->debug();
    }

    private function debug(): bool
    {
        return (bool) $this->config->get('app.debug', false);
    }
}
