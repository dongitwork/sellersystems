<?php

namespace Modules\Products\Controllers;

use App\Core\BaseController;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductPrice;
use Modules\Products\Models\LoyaltyPrice;
use Modules\Products\Models\LoyaltyTier;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class PriceController extends BaseController
{
    private Product $productModel;
    private ProductPrice $priceModel;
    private LoyaltyPrice $loyaltyPriceModel;
    private LoyaltyTier $loyaltyTierModel;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->productModel = new Product();
        $this->priceModel = new ProductPrice();
        $this->loyaltyPriceModel = new LoyaltyPrice();
        $this->loyaltyTierModel = new LoyaltyTier();
    }

    /**
     * Display price management page
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id'];
        $product = $this->productModel->getWithDetails($productId);

        if (!$product) {
            $this->flash('danger', 'Product not found');
            return $this->redirect($response, '/products');
        }

        // Get loyalty tiers
        $tiers = $this->loyaltyTierModel->getActive();

        // Get loyalty prices grouped by tier
        $loyaltyPrices = $this->loyaltyPriceModel->getByProductGrouped($productId);

        return $this->render($response, 'modules/Products/Views/prices.php', [
            'product' => $product,
            'tiers' => $tiers,
            'loyaltyPrices' => $loyaltyPrices
        ]);
    }

    /**
     * Create base price
     */
    public function storeBasePrice(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id'];
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['size'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Size is required'
            ], 400);
        }

        // Check if price exists
        if ($this->priceModel->priceExists($productId, $data['size'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Price for this size already exists'
            ], 400);
        }

        // Create price
        $priceId = $this->priceModel->create([
            'product_id' => $productId,
            'size' => $data['size'],
            'price' => $data['price'] ?? 0.00,
            'extra_price' => $data['extra_price'] ?? 0.00,
            'rush_fee' => $data['rush_fee'] ?? 1.49,
            'shipping_fee' => $data['shipping_fee'] ?? 0.00,
            'extra_shipping_fee' => $data['extra_shipping_fee'] ?? 0.00,
            'priority_fee' => $data['priority_fee'] ?? 0.00,
            'extra_priority_fee' => $data['extra_priority_fee'] ?? 0.00,
            'label_fee' => $data['label_fee'] ?? 0.30,
            'special_fee' => $data['special_fee'] ?? 2.00,
            'is_active' => 1
        ]);

        if (!$priceId) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to create price'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Price created successfully',
            'price_id' => $priceId
        ]);
    }

    /**
     * Update base price
     */
    public function updateBasePrice(Request $request, Response $response, array $args): Response
    {
        $priceId = $args['price_id'];
        $data = $request->getParsedBody();

        // Get current price
        $price = $this->priceModel->find($priceId);
        if (!$price) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Price not found'
            ], 404);
        }

        // Update price
        $updated = $this->priceModel->update($priceId, [
            'price' => $data['price'] ?? $price['price'],
            'extra_price' => $data['extra_price'] ?? $price['extra_price'],
            'rush_fee' => $data['rush_fee'] ?? $price['rush_fee'],
            'shipping_fee' => $data['shipping_fee'] ?? $price['shipping_fee'],
            'extra_shipping_fee' => $data['extra_shipping_fee'] ?? $price['extra_shipping_fee'],
            'priority_fee' => $data['priority_fee'] ?? $price['priority_fee'],
            'extra_priority_fee' => $data['extra_priority_fee'] ?? $price['extra_priority_fee'],
            'label_fee' => $data['label_fee'] ?? $price['label_fee'],
            'special_fee' => $data['special_fee'] ?? $price['special_fee']
        ]);

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to update price'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Price updated successfully'
        ]);
    }

    /**
     * Quick edit price field (for inline editing)
     */
    public function quickEdit(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['id']) || empty($data['field']) || !isset($data['value'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Invalid parameters'
            ], 400);
        }

        $type = $data['type'] ?? 'base'; // base or loyalty
        $id = $data['id'];
        $field = $data['field'];
        $value = $data['value'] === '' ? null : floatval($data['value']);

        // Update based on type
        if ($type === 'loyalty') {
            $updated = $this->loyaltyPriceModel->quickUpdate($id, $field, $value);
        } else {
            $updated = $this->priceModel->quickUpdate($id, $field, $value);
        }

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to update price'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Price updated successfully',
            'value' => $value
        ]);
    }

    /**
     * Delete base price
     */
    public function deleteBasePrice(Request $request, Response $response, array $args): Response
    {
        $priceId = $args['price_id'];

        // Check if price exists
        $price = $this->priceModel->find($priceId);
        if (!$price) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Price not found'
            ], 404);
        }

        // Delete price (cascade will delete loyalty prices too)
        $deleted = $this->priceModel->delete($priceId);

        if (!$deleted) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to delete price'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Price deleted successfully'
        ]);
    }

    /**
     * Create/Update loyalty price
     */
    public function storeLoyaltyPrice(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id'];
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['tier_id']) || empty($data['size'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier and size are required'
            ], 400);
        }

        // Check if base price exists
        if (!$this->priceModel->priceExists($productId, $data['size'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Base price must be created first'
            ], 400);
        }

        // Check if loyalty price exists
        $existing = $this->loyaltyPriceModel->getByProductTierSize(
            $productId,
            $data['tier_id'],
            $data['size']
        );

        $priceData = [
            'product_id' => $productId,
            'tier_id' => $data['tier_id'],
            'size' => $data['size'],
            'price' => $data['price'] ?? null,
            'extra_price' => $data['extra_price'] ?? null,
            'rush_fee' => $data['rush_fee'] ?? null,
            'shipping_fee' => $data['shipping_fee'] ?? null,
            'extra_shipping_fee' => $data['extra_shipping_fee'] ?? null,
            'priority_fee' => $data['priority_fee'] ?? null,
            'extra_priority_fee' => $data['extra_priority_fee'] ?? null,
            'label_fee' => $data['label_fee'] ?? null,
            'special_fee' => $data['special_fee'] ?? null,
            'is_active' => 1
        ];

        if ($existing) {
            // Update existing
            $result = $this->loyaltyPriceModel->update($existing['id'], $priceData);
            $message = 'Loyalty price updated successfully';
        } else {
            // Create new
            $result = $this->loyaltyPriceModel->create($priceData);
            $message = 'Loyalty price created successfully';
        }

        if (!$result) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to save loyalty price'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => $message
        ]);
    }
}
