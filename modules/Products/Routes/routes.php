<?php
namespace Modules\Products\Routes;

use Slim\App;
use DI\Container;
use Modules\Products\Controllers\ProductController;
use Modules\Products\Controllers\PriceController;
use Modules\Products\Controllers\ColorController;
use Modules\Products\Controllers\LoyaltyTierController;
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
        // Product routes
        $this->app->group('/products', function($group) {
            // Product list and create (no {id} parameter)
            $group->get('', ProductController::class . ':index');
            $group->post('', ProductController::class . ':store');

            // Loyalty tier management (static routes must come before variable routes)
            $group->get('/loyalty-tiers', LoyaltyTierController::class . ':index');
            $group->post('/loyalty-tiers', LoyaltyTierController::class . ':store');
            $group->get('/loyalty-tiers/{id}/edit', LoyaltyTierController::class . ':edit');
            $group->post('/loyalty-tiers/{id}/update', LoyaltyTierController::class . ':update');
            $group->post('/loyalty-tiers/{id}/delete', LoyaltyTierController::class . ':delete');
            $group->post('/loyalty-tiers/{id}/toggle', LoyaltyTierController::class . ':toggleStatus');

            // Price static routes (before {id})
            $group->post('/prices/{price_id}/update', PriceController::class . ':updateBasePrice');
            $group->post('/prices/{price_id}/delete', PriceController::class . ':deleteBasePrice');
            $group->post('/prices/quick-edit', PriceController::class . ':quickEdit');

            // Color static routes (before {id})
            $group->post('/colors/{color_id}/update', ColorController::class . ':update');
            $group->post('/colors/{color_id}/delete', ColorController::class . ':delete');

            // Product CRUD with {id} parameter (must come after static routes)
            $group->get('/{id}', ProductController::class . ':show');
            $group->get('/{id}/edit', ProductController::class . ':edit');
            $group->post('/{id}/update', ProductController::class . ':update');
            $group->post('/{id}/delete', ProductController::class . ':delete');

            // Price management with {id}
            $group->get('/{id}/prices', PriceController::class . ':index');
            $group->post('/{id}/prices', PriceController::class . ':storeBasePrice');
            $group->post('/{id}/loyalty-prices', PriceController::class . ':storeLoyaltyPrice');

            // Color management with {id}
            $group->get('/{id}/colors', ColorController::class . ':index');
            $group->post('/{id}/colors', ColorController::class . ':store');
        })->add(new AuthMiddleware());
    }
}
