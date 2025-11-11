<?php
namespace Modules\User\Routes;

use Slim\App;
use DI\Container;
use Modules\User\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

class Routes {
    private App $app;
    private Container $container;
    
    public function __construct(App $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }
    
    public function register(): void {
        $this->app->group('/users', function($group) {
            $group->get('', UserController::class . ':index');
            $group->get('/create', UserController::class . ':create');
            $group->post('', UserController::class . ':store');
            $group->get('/{id}/edit', UserController::class . ':edit');
            $group->post('/{id}', UserController::class . ':update');
            $group->post('/{id}/delete', UserController::class . ':delete');
        })->add(new RoleMiddleware(['admin']))
          ->add(new AuthMiddleware());
    }
}