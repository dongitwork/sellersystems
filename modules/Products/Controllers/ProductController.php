<?php

namespace Modules\Products\Controllers;

use App\Core\BaseController;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductColor;
use Modules\Products\Models\ProductPrice;
use Modules\Products\Models\LoyaltyTier;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class ProductController extends BaseController
{
    private Product $productModel;
    private ProductColor $colorModel;
    private ProductPrice $priceModel;
    private LoyaltyTier $loyaltyModel;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->productModel = new Product();
        $this->colorModel = new ProductColor();
        $this->priceModel = new ProductPrice();
        $this->loyaltyModel = new LoyaltyTier();
    }

    /**
     * Display products list
     */
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $search = $params['search'] ?? null;
        $filters = [
            'brand' => $params['brand'] ?? null,
            'category' => $params['category'] ?? null
        ];

        $result = $this->productModel->paginate($page, 15, $search, $filters);
        $brands = $this->productModel->getBrands();
        $categories = $this->productModel->getCategories();

        return $this->render($response, 'modules/Products/Views/index.php', [
            'products' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'total' => $result['total']
            ],
            'brands' => $brands,
            'categories' => $categories,
            'search' => $search,
            'filters' => $filters
        ]);
    }

    /**
     * Show product details
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $product = $this->productModel->getWithDetails($id);

        if (!$product) {
            $this->flash('danger', 'Product not found');
            return $this->redirect($response, '/products');
        }

        return $this->render($response, 'modules/Products/Views/show.php', [
            'product' => $product
        ]);
    }

    /**
     * Create new product
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['product_name']) || empty($data['brand']) ||
            empty($data['category']) || empty($data['style'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Please fill all required fields'
            ], 400);
        }

        // Check if style exists
        if ($this->productModel->styleExists($data['style'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Style already exists'
            ], 400);
        }

        // Create product
        $productId = $this->productModel->create([
            'product_name' => $data['product_name'],
            'brand' => $data['brand'],
            'category' => $data['category'],
            'style' => $data['style'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_active' => 1
        ]);

        if (!$productId) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to create product'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Product created successfully',
            'product_id' => $productId
        ]);
    }

    /**
     * Get product for editing
     */
    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $product = $this->productModel->getWithDetails($id);

        if (!$product) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * Update product
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['product_name']) || empty($data['brand']) ||
            empty($data['category']) || empty($data['style'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Please fill all required fields'
            ], 400);
        }

        // Check if style exists (excluding current product)
        if ($this->productModel->styleExists($data['style'], $id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Style already exists'
            ], 400);
        }

        // Update product
        $updated = $this->productModel->update($id, [
            'product_name' => $data['product_name'],
            'brand' => $data['brand'],
            'category' => $data['category'],
            'style' => $data['style'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null
        ]);

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to update product'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Delete product
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // Check if product exists
        $product = $this->productModel->find($id);
        if (!$product) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Soft delete (set is_active = 0)
        $deleted = $this->productModel->update($id, ['is_active' => 0]);

        if (!$deleted) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to delete product'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
