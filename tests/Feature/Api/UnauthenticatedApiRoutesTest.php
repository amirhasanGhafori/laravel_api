<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\DataProvider;

class UnauthenticatedApiRoutesTest extends ApiTestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedRoutes(): array
    {
        return [
            'POST /api/logout' => ['postJson', '/api/logout'],
            'GET /api/user' => ['getJson', '/api/user'],
            'GET /api/v1/user' => ['getJson', '/api/v1/user'],
            'GET /api/v1/tickets' => ['getJson', '/api/v1/tickets'],
            'POST /api/v1/tickets' => ['postJson', '/api/v1/tickets'],
            'GET /api/v1/tickets/{ticket}' => ['getJson', '/api/v1/tickets/1'],
            'PATCH /api/v1/tickets/{ticket}' => ['patchJson', '/api/v1/tickets/1'],
            'PUT /api/v1/tickets/{ticket}' => ['putJson', '/api/v1/tickets/1'],
            'DELETE /api/v1/tickets/{ticket}' => ['deleteJson', '/api/v1/tickets/1'],
            'GET /api/v1/users/{user}/tickets' => ['getJson', '/api/v1/users/1/tickets'],
            'POST /api/v1/users/{user}/tickets' => ['postJson', '/api/v1/users/1/tickets'],
            'GET /api/v1/users/{user}/tickets/{ticket}' => ['getJson', '/api/v1/users/1/tickets/1'],
            'PATCH /api/v1/users/{user}/tickets/{ticket}' => ['patchJson', '/api/v1/users/1/tickets/1'],
            'PUT /api/v1/users/{user}/tickets/{ticket}' => ['putJson', '/api/v1/users/1/tickets/1'],
            'DELETE /api/v1/users/{user}/tickets/{ticket}' => ['deleteJson', '/api/v1/users/1/tickets/1'],
            'GET /api/v1/users' => ['getJson', '/api/v1/users'],
            'POST /api/v1/users' => ['postJson', '/api/v1/users'],
            'GET /api/v1/users/{user}' => ['getJson', '/api/v1/users/1'],
            'PATCH /api/v1/users/{user}' => ['patchJson', '/api/v1/users/1'],
            'PUT /api/v1/users/{users}' => ['putJson', '/api/v1/users/1'],
            'DELETE /api/v1/users/{user}' => ['deleteJson', '/api/v1/users/1'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_it_returns_401_when_unauthenticated(string $method, string $uri): void
    {
        $this->{$method}($uri)->assertUnauthorized();
    }
}
