<?php
namespace Modules\Auth\Routes;

use Slim\App;
use DI\Container;
use Modules\Auth\Controllers\AuthController;
use App\Middleware\GuestMiddleware;
use App\Middleware\AuthMiddleware;

class Routes {
    private App $app;
    private Container $container;
    
    public function __construct(App $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }
    
    public function register(): void {
        $this->app->group('', function($group) {
            $group->get('/login', AuthController::class . ':loginForm');
            $group->post('/login', AuthController::class . ':login');
            $group->get('/register', AuthController::class . ':registerForm');
            $group->post('/register', AuthController::class . ':register');
        })->add(new GuestMiddleware());
        
        $this->app->post('/logout', AuthController::class . ':logout')
            ->add(new AuthMiddleware());
    }
}