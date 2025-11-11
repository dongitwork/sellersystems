<?php

namespace Modules\Products\Models;

use App\Core\BaseModel;

class Product extends BaseModel
{
    protected string $table = 'srs_products';

    protected array $fillable = [
        'product_name',
        'brand',
        'category',
        'style',
        'description',
        'image_url',
        'is_active',
        'meta_data'
    ];

    protected array $hidden = [];

    /**
     * Get all products with pagination
     */
    public function paginate($page = 1, $perPage = 15, $search = null, $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        $where = ['is_active' => 1];

        // Add search condition
        if ($search) {
            $where['OR'] = [
                'product_name[~]' => $search,
                'brand[~]' => $search,
                'category[~]' => $search,
                'style[~]' => $search
            ];
        }

        // Add filters
        if (!empty($filters['brand'])) {
            $where['brand'] = $filters['brand'];
        }
        if (!empty($filters['category'])) {
            $where['category'] = $filters['category'];
        }

        $where['LIMIT'] = [$offset, $perPage];
        $where['ORDER'] = ['created_at' => 'DESC'];

        $products = $this->db->select($this->table, '*', $where);

        // Get total count
        unset($where['LIMIT'], $where['ORDER']);
        $total = $this->db->count($this->table, $where);

        return [
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get product with colors and prices
     */
    public function getWithDetails($id)
    {
        $product = $this->find($id);

        if (!$product) {
            return null;
        }

        // Get colors
        $product['colors'] = $this->db->select('srs_product_colors', '*', [
            'product_id' => $id,
            'is_active' => 1,
            'ORDER' => ['created_at' => 'ASC']
        ]);

        // Get base prices
        $product['prices'] = $this->db->select('srs_product_prices', '*', [
            'product_id' => $id,
            'is_active' => 1,
            'ORDER' => ['size' => 'ASC']
        ]);

        return $product;
    }

    /**
     * Get all brands
     */
    public function getBrands()
    {
        return $this->db->select($this->table, 'brand', [
            'is_active' => 1,
            'GROUP' => 'brand',
            'ORDER' => ['brand' => 'ASC']
        ]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        return $this->db->select($this->table, 'category', [
            'is_active' => 1,
            'GROUP' => 'category',
            'ORDER' => ['category' => 'ASC']
        ]);
    }

    /**
     * Check if style exists
     */
    public function styleExists($style, $excludeId = null)
    {
        $where = ['style' => $style];

        if ($excludeId) {
            $where['id[!]'] = $excludeId;
        }

        return $this->db->has($this->table, $where);
    }
}
