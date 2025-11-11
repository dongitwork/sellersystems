<?php

namespace Modules\Products\Models;

use App\Core\BaseModel;

class ProductPrice extends BaseModel
{
    protected string $table = 'srs_product_prices';

    protected array $fillable = [
        'product_id',
        'size',
        'price',
        'extra_price',
        'rush_fee',
        'shipping_fee',
        'extra_shipping_fee',
        'priority_fee',
        'extra_priority_fee',
        'label_fee',
        'special_fee',
        'is_active',
        'meta_data'
    ];

    protected array $hidden = [];

    /**
     * Get prices by product
     */
    public function getByProduct($productId)
    {
        return $this->db->select($this->table, '*', [
            'product_id' => $productId,
            'ORDER' => ['size' => 'ASC']
        ]);
    }

    /**
     * Get price by product and size
     */
    public function getByProductAndSize($productId, $size)
    {
        return $this->db->get($this->table, '*', [
            'product_id' => $productId,
            'size' => $size
        ]);
    }

    /**
     * Check if price exists for product and size
     */
    public function priceExists($productId, $size, $excludeId = null)
    {
        $where = [
            'product_id' => $productId,
            'size' => $size
        ];

        if ($excludeId) {
            $where['id[!]'] = $excludeId;
        }

        return $this->db->has($this->table, $where);
    }

    /**
     * Bulk create prices
     */
    public function bulkCreate($productId, $prices)
    {
        $data = [];

        foreach ($prices as $price) {
            $data[] = [
                'product_id' => $productId,
                'size' => $price['size'],
                'price' => $price['price'] ?? 0.00,
                'extra_price' => $price['extra_price'] ?? 0.00,
                'rush_fee' => $price['rush_fee'] ?? 1.49,
                'shipping_fee' => $price['shipping_fee'] ?? 0.00,
                'extra_shipping_fee' => $price['extra_shipping_fee'] ?? 0.00,
                'priority_fee' => $price['priority_fee'] ?? 0.00,
                'extra_priority_fee' => $price['extra_priority_fee'] ?? 0.00,
                'label_fee' => $price['label_fee'] ?? 0.30,
                'special_fee' => $price['special_fee'] ?? 2.00,
                'is_active' => 1
            ];
        }

        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->table, $data);
        return true;
    }

    /**
     * Quick update price field
     */
    public function quickUpdate($id, $field, $value)
    {
        $allowedFields = [
            'price', 'extra_price', 'rush_fee', 'shipping_fee',
            'extra_shipping_fee', 'priority_fee', 'extra_priority_fee',
            'label_fee', 'special_fee'
        ];

        if (!in_array($field, $allowedFields)) {
            return false;
        }

        return $this->update($id, [$field => $value]);
    }
}
