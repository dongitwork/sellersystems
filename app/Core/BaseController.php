<?php
namespace App\Core;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

abstract class BaseController {
    protected Container $container;
    protected $db;
    protected $view;
    
    public function __construct(Container $container) {
        $this->container = $container;
        $this->db = $container->get('db');
        $this->view = $container->get('view');
    }
    
    protected function json(Response $response, $data, int $status = 200): Response {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
    
    protected function redirect(Response $response, string $url): Response {
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
    
    protected function render(Response $response, string $template, array $data = []): Response {
        $html = $this->view->render($template, $data);
        $response->getBody()->write($html);
        return $response;
    }
    
    protected function flash(string $type, string $message): void {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}