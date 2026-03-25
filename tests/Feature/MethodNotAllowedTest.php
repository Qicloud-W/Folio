<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class MethodNotAllowedTest extends TestCase
{
    public function test_known_path_with_wrong_method_returns_405_json(): void
    {
        [$statusCode, $headers, $body] = $this->dispatch('POST', '/api/v1/ping');

        self::assertSame(405, $statusCode);
        self::assertContains('Content-Type: application/json; charset=utf-8', $headers);
        self::assertContains('Allow: GET', $headers);
        self::assertJson($body);
        self::assertSame(
            [
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Method not allowed',
                    'allowed_methods' => ['GET'],
                ],
            ],
            json_decode($body, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return array{0:int,1:list<string>,2:string}
     */
    private function dispatch(string $method, string $uri): array
    {
        $_SERVER['REQUEST_METHOD'] = $method;
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
