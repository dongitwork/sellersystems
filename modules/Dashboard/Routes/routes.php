<?php
namespace Modules\Dashboard\Routes;

use Slim\App;
use DI\Container;
use Modules\Dashboard\Controllers\DashboardController;
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
            $group->get('/', DashboardController::class . ':index');
            $group->get('/dashboard', DashboardController::class . ':index');
        })->add(new AuthMiddleware());
    }
}