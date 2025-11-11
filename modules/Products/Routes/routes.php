<?php

use Modules\Products\Controllers\ProductController;
use Modules\Products\Controllers\PriceController;
use Modules\Products\Controllers\ColorController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;

return function ($app) {
    // Product routes
    $app->group('/products', function ($group) {
        // Product CRUD
        $group->get('', ProductController::class . ':index');
        $group->get('/{id}', ProductController::class . ':show');
        $group->post('', ProductController::class . ':store');
        $group->get('/{id}/edit', ProductController::class . ':edit');
        $group->post('/{id}/update', ProductController::class . ':update');
        $group->post('/{id}/delete', ProductController::class . ':delete');

        // Price management
        $group->get('/{id}/prices', PriceController::class . ':index');
        $group->post('/{id}/prices', PriceController::class . ':storeBasePrice');
        $group->post('/prices/{price_id}/update', PriceController::class . ':updateBasePrice');
        $group->post('/prices/{price_id}/delete', PriceController::class . ':deleteBasePrice');
        $group->post('/prices/quick-edit', PriceController::class . ':quickEdit');

        // Loyalty price management
        $group->post('/{id}/loyalty-prices', PriceController::class . ':storeLoyaltyPrice');

        // Color management
        $group->get('/{id}/colors', ColorController::class . ':index');
        $group->post('/{id}/colors', ColorController::class . ':store');
        $group->post('/colors/{color_id}/update', ColorController::class . ':update');
        $group->post('/colors/{color_id}/delete', ColorController::class . ':delete');
    })->add(new AuthMiddleware());
};
