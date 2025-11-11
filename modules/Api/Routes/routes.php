<?php
namespace Modules\Api\Routes;

use Slim\App;
use DI\Container;
use Modules\Api\Controllers\ApiAuthController;
use Modules\Api\Controllers\ApiUserController;
use App\Middleware\ApiAuthMiddleware;

class Routes {
    private App $app;
    private Container $container;

    public function __construct(App $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }

    public function register(): void {
        // Public API routes (no authentication required)
        $this->app->group('/api', function($group) {
            $group->post('/login', ApiAuthController::class . ':login');
            $group->post('/register', ApiAuthController::class . ':register');
        });

        // Protected API routes (authentication required)
        $this->app->group('/api', function($group) {
            // Auth routes
            $group->post('/logout', ApiAuthController::class . ':logout');

            // User management routes (admin only)
            $group->get('/users', ApiUserController::class . ':index');
            $group->get('/users/{id}', ApiUserController::class . ':show');
            $group->post('/users', ApiUserController::class . ':store');
            $group->put('/users/{id}', ApiUserController::class . ':update');
            $group->delete('/users/{id}', ApiUserController::class . ':destroy');
        })->add(new ApiAuthMiddleware());
    }
}
