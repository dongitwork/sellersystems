<?php

namespace Modules\Products\Models;

use App\Core\BaseModel;

class LoyaltyPrice extends BaseModel
{
    protected string $table = 'srs_loyalty_prices';

    protected array $fillable = [
        'product_id',
        'tier_id',
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
     * Get loyalty prices by product and tier
     */
    public function getByProductAndTier($productId, $tierId)
    {
        return $this->db->select($this->table, '*', [
            'product_id' => $productId,
            'tier_id' => $tierId,
            'ORDER' => ['size' => 'ASC']
        ]);
    }

    /**
     * Get loyalty price by product, tier and size
     */
    public function getByProductTierSize($productId, $tierId, $size)
    {
        return $this->db->get($this->table, '*', [
            'product_id' => $productId,
            'tier_id' => $tierId,
            'size' => $size
        ]);
    }

    /**
     * Get all loyalty prices for a product (grouped by tier)
     */
    public function getByProductGrouped($productId)
    {
        $prices = $this->db->select($this->table, [
            '[>]srs_loyalty_tiers' => ['tier_id' => 'id']
        ], [
            'srs_loyalty_prices.id',
            'srs_loyalty_prices.product_id',
            'srs_loyalty_prices.tier_id',
            'srs_loyalty_prices.size',
            'srs_loyalty_prices.price',
            'srs_loyalty_prices.extra_price',
            'srs_loyalty_prices.rush_fee',
            'srs_loyalty_prices.shipping_fee',
            'srs_loyalty_prices.extra_shipping_fee',
            'srs_loyalty_prices.priority_fee',
            'srs_loyalty_prices.extra_priority_fee',
            'srs_loyalty_prices.label_fee',
            'srs_loyalty_prices.special_fee',
            'srs_loyalty_tiers.tier_name',
            'srs_loyalty_tiers.tier_slug'
        ], [
            'srs_loyalty_prices.product_id' => $productId,
            'srs_loyalty_prices.is_active' => 1,
            'ORDER' => [
                'srs_loyalty_tiers.sort_order' => 'ASC',
                'srs_loyalty_prices.size' => 'ASC'
            ]
        ]);

        // Group by tier
        $grouped = [];
        foreach ($prices as $price) {
            $tierId = $price['tier_id'];
            if (!isset($grouped[$tierId])) {
                $grouped[$tierId] = [
                    'tier_id' => $tierId,
                    'tier_name' => $price['tier_name'],
                    'tier_slug' => $price['tier_slug'],
                    'prices' => []
                ];
            }
            $grouped[$tierId]['prices'][] = $price;
        }

        return array_values($grouped);
    }

    /**
     * Check if loyalty price exists
     */
    public function priceExists($productId, $tierId, $size, $excludeId = null)
    {
        $where = [
            'product_id' => $productId,
            'tier_id' => $tierId,
            'size' => $size
        ];

        if ($excludeId) {
            $where['id[!]'] = $excludeId;
        }

        return $this->db->has($this->table, $where);
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

        // Allow NULL values for loyalty prices (to fall back to base price)
        return $this->update($id, [$field => $value]);
    }
}
