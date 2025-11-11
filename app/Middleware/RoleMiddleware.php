<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware {
    private array $roles;
    
    public function __construct(array $roles) {
        $this->roles = $roles;
    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response {
        if (!isset($_SESSION['user_roles'])) {
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }
        
        $userRoles = $_SESSION['user_roles'] ?? [];
        $hasRole = !empty(array_intersect($this->roles, $userRoles));
        
        if (!$hasRole) {
            $response = new SlimResponse();
            $response->getBody()->write('Unauthorized');
            return $response->withStatus(403);
        }
        
        return $handler->handle($request);
    }
}