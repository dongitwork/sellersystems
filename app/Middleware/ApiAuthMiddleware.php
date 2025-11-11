<?php

namespace App\Middleware;

use App\Core\BaseModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class ApiAuthMiddleware implements MiddlewareInterface
{
    private BaseModel $model;

    public function __construct()
    {
        $this->model = new BaseModel();
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Get Authorization header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->unauthorized('No authorization header provided');
        }

        // Check if it's a Bearer token
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $this->unauthorized('Invalid authorization header format. Use: Bearer {token}');
        }

        $token = $matches[1];

        if (empty($token)) {
            return $this->unauthorized('Token is empty');
        }

        // Find user by token
        $user = $this->model->db()->get('users', '*', [
            'api_token' => $token,
            'status' => 'active'
        ]);

        if (!$user) {
            return $this->unauthorized('Invalid or expired token');
        }

        // Get user roles
        $roles = $this->model->db()->select('user_roles', [
            '[>]roles' => ['role_id' => 'id']
        ], [
            'roles.slug'
        ], [
            'user_roles.user_id' => $user['id']
        ]);

        $user['roles'] = array_column($roles, 'slug');

        // Add user to request attributes
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('user_id', $user['id']);
        $request = $request->withAttribute('user_roles', $user['roles']);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $message
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
