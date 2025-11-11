<?php

namespace Modules\Products\Models;

use App\Core\BaseModel;

class ProductColor extends BaseModel
{
    protected string $table = 'srs_product_colors';

    protected array $fillable = [
        'product_id',
        'color_name',
        'color_code',
        'color_image',
        'is_active',
        'meta_data'
    ];

    protected array $hidden = [];

    /**
     * Get colors by product
     */
    public function getByProduct($productId)
    {
        return $this->db->select($this->table, '*', [
            'product_id' => $productId,
            'ORDER' => ['created_at' => 'ASC']
        ]);
    }

    /**
     * Check if color exists for product
     */
    public function colorExists($productId, $colorName, $excludeId = null)
    {
        $where = [
            'product_id' => $productId,
            'color_name' => $colorName
        ];

        if ($excludeId) {
            $where['id[!]'] = $excludeId;
        }

        return $this->db->has($this->table, $where);
    }

    /**
     * Bulk create colors
     */
    public function bulkCreate($productId, $colors)
    {
        $data = [];

        foreach ($colors as $color) {
            $data[] = [
                'product_id' => $productId,
                'color_name' => $color['color_name'],
                'color_code' => $color['color_code'],
                'color_image' => $color['color_image'] ?? null,
                'is_active' => 1
            ];
        }

        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->table, $data);
        return true;
    }
}
