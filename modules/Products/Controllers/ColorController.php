<?php

namespace Modules\Products\Controllers;

use App\Core\BaseController;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductColor;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ColorController extends BaseController
{
    private Product $productModel;
    private ProductColor $colorModel;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->productModel = new Product($this->db);
        $this->colorModel = new ProductColor($this->db);
    }

    /**
     * Get colors for a product
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id'];
        $colors = $this->colorModel->getByProduct($productId);

        return $this->json($response, [
            'success' => true,
            'colors' => $colors
        ]);
    }

    /**
     * Create new color
     */
    public function store(Request $request, Response $response, array $args): Response
    {
        $productId = $args['id'];
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['color_name']) || empty($data['color_code'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color name and code are required'
            ], 400);
        }

        // Check if color exists
        if ($this->colorModel->colorExists($productId, $data['color_name'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color already exists for this product'
            ], 400);
        }

        // Create color
        $colorId = $this->colorModel->create([
            'product_id' => $productId,
            'color_name' => $data['color_name'],
            'color_code' => $data['color_code'],
            'color_image' => $data['color_image'] ?? null,
            'is_active' => 1
        ]);

        if (!$colorId) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to create color'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Color created successfully',
            'color_id' => $colorId
        ]);
    }

    /**
     * Update color
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $colorId = $args['color_id'];
        $data = $request->getParsedBody();

        // Get current color
        $color = $this->colorModel->find($colorId);
        if (!$color) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color not found'
            ], 404);
        }

        // Validation
        if (empty($data['color_name']) || empty($data['color_code'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color name and code are required'
            ], 400);
        }

        // Check if color name exists (excluding current)
        if ($this->colorModel->colorExists($color['product_id'], $data['color_name'], $colorId)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color already exists for this product'
            ], 400);
        }

        // Update color
        $updated = $this->colorModel->update($colorId, [
            'color_name' => $data['color_name'],
            'color_code' => $data['color_code'],
            'color_image' => $data['color_image'] ?? $color['color_image']
        ]);

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to update color'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Color updated successfully'
        ]);
    }

    /**
     * Delete color
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $colorId = $args['color_id'];

        // Check if color exists
        $color = $this->colorModel->find($colorId);
        if (!$color) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Color not found'
            ], 404);
        }

        // Delete color
        $deleted = $this->colorModel->delete($colorId);

        if (!$deleted) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to delete color'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Color deleted successfully'
        ]);
    }
}
