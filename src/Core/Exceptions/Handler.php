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
        return array_filter([
            'code' => $this->code($exception),
            'message' => $this->message($exception),
            'meta' => $this->meta($exception),
            'context' => $this->context($request),
            'debug' => $this->debug($exception),
            'report' => $this->reportable($exception),
            'render' => $this->renderable($exception),
        ], static fn (mixed $value): bool => $value !== null);
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
        if (!$this->debugEnabled() && $this->status($exception) >= 500) {
            return 'Internal Server Error';
        }

        return $exception->getMessage();
    }

    private function meta(Throwable $exception): ?array
    {
        if (!$exception instanceof HttpException) {
            return null;
        }

        $meta = $exception->meta();

        return $meta === [] ? null : $meta;
    }

    private function context(Request $request): array
    {
        return array_filter([
            'method' => $request->method(),
            'path' => $request->path(),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function debug(Throwable $exception): ?array
    {
        if (!$this->debugEnabled()) {
            return null;
        }

        return [
            'exception' => $exception::class,
        ];
    }

    private function reportable(Throwable $exception): array
    {
        return [
            'should_report' => !$exception instanceof HttpException || $exception->status() >= 500,
        ];
    }

    private function renderable(Throwable $exception): array
    {
        return [
            'status' => $this->status($exception),
            'headers' => $this->headers($exception),
        ];
    }

    private function debugEnabled(): bool
    {
        return (bool) $this->config->get('app.debug', false);
    }
}
