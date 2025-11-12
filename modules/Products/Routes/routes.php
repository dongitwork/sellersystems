<?php
namespace Modules\Products\Routes;

use Slim\App;
use DI\Container;
use Modules\Products\Controllers\ProductController;
use Modules\Products\Controllers\PriceController;
use Modules\Products\Controllers\ColorController;
use Modules\Products\Controllers\LoyaltyTierController;
use App\Middleware\AuthMiddleware;

class Routes {
    private App $app;
    private Container $container;

    public function __construct(App $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }

    public function register(): void {
        $this->app->group('/products', function($group) {
            // Product list
            $group->get('', ProductController::class . ':index');
            $group->post('', ProductController::class . ':store');

            // Loyalty tiers
            $group->get('/loyalty-tiers', LoyaltyTierController::class . ':index');
            $group->post('/loyalty-tiers', LoyaltyTierController::class . ':store');
            $group->get('/loyalty-tiers/{id}/edit', LoyaltyTierController::class . ':edit');
            $group->post('/loyalty-tiers/{id}/update', LoyaltyTierController::class . ':update');
            $group->post('/loyalty-tiers/{id}/delete', LoyaltyTierController::class . ':delete');
            $group->post('/loyalty-tiers/{id}/toggle', LoyaltyTierController::class . ':toggleStatus');

            // Prices
            $group->post('/prices/{price_id}/update', PriceController::class . ':updateBasePrice');
            $group->post('/prices/{price_id}/delete', PriceController::class . ':deleteBasePrice');
            $group->post('/prices/quick-edit', PriceController::class . ':quickEdit');

            // Colors
            $group->post('/colors/{color_id}/update', ColorController::class . ':update');
            $group->post('/colors/{color_id}/delete', ColorController::class . ':delete');

            // Product detail
            $group->get('/{id}', ProductController::class . ':show');
            $group->get('/{id}/edit', ProductController::class . ':edit');
            $group->post('/{id}/update', ProductController::class . ':update');
            $group->post('/{id}/delete', ProductController::class . ':delete');
            $group->get('/{id}/prices', PriceController::class . ':index');
            $group->post('/{id}/prices', PriceController::class . ':storeBasePrice');
            $group->post('/{id}/loyalty-prices', PriceController::class . ':storeLoyaltyPrice');
            $group->get('/{id}/colors', ColorController::class . ':index');
            $group->post('/{id}/colors', ColorController::class . ':store');
        })->add(new AuthMiddleware());
    }
}
