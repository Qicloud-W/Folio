<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class NotFoundEndpointTest extends TestCase
{
    public function test_unknown_route_returns_expected_404_json(): void
    {
        [$statusCode, $headers, $body] = $this->dispatch('/missing-route');

        self::assertSame(404, $statusCode);
        self::assertContains('Content-Type: application/json; charset=utf-8', $headers);
        self::assertJson($body);
        self::assertSame(
            [
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Route not found',
                ],
            ],
            json_decode($body, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return array{0:int,1:list<string>,2:string}
     */
    private function dispatch(string $uri): array
    {
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_HOST'] = 'localhost';

        http_response_code(200);
        header_remove();

        ob_start();
        require __DIR__ . '/../../public/index.php';
        $body = (string) ob_get_clean();

        return [http_response_code(), headers_list(), $body];
    }
}
