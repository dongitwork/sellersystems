<?php

namespace Modules\Products\Models;

use App\Core\BaseModel;

class LoyaltyTier extends BaseModel
{
    protected string $table = 'srs_loyalty_tiers';

    protected array $fillable = [
        'tier_name',
        'tier_slug',
        'min_orders',
        'min_amount',
        'discount_percent',
        'description',
        'is_active',
        'sort_order'
    ];

    protected array $hidden = [];

    /**
     * Get all active tiers
     */
    public function getActive()
    {
        return $this->db->select($this->table, '*', [
            'is_active' => 1,
            'ORDER' => ['sort_order' => 'ASC']
        ]);
    }

    /**
     * Get tier by slug
     */
    public function getBySlug($slug)
    {
        return $this->db->get($this->table, '*', [
            'tier_slug' => $slug
        ]);
    }

    /**
     * Check if slug exists
     */
    public function slugExists($slug, $excludeId = null)
    {
        $where = ['tier_slug' => $slug];

        if ($excludeId) {
            $where['id[!]'] = $excludeId;
        }

        return $this->db->has($this->table, $where);
    }
}
