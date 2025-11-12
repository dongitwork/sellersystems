<?php

namespace Modules\Products\Controllers;

use App\Core\BaseController;
use Modules\Products\Models\LoyaltyTier;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use DI\Container;

class LoyaltyTierController extends BaseController
{
    private LoyaltyTier $tierModel;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->tierModel = new LoyaltyTier();
    }

    /**
     * Display loyalty tiers list
     */
    public function index(Request $request, Response $response): Response
    {
        $tiers = $this->tierModel->all();

        return $this->render($response, 'Products:loyalty_tiers', [
            'tiers' => $tiers ?: []
        ]);
    }

    /**
     * Create new tier
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['tier_name']) || empty($data['tier_slug'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier name and slug are required'
            ], 400);
        }

        // Check if slug exists
        if ($this->tierModel->slugExists($data['tier_slug'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier slug already exists'
            ], 400);
        }

        // Create tier
        $tierId = $this->tierModel->create([
            'tier_name' => $data['tier_name'],
            'tier_slug' => $data['tier_slug'],
            'min_orders' => $data['min_orders'] ?? 0,
            'min_amount' => $data['min_amount'] ?? 0.00,
            'discount_percent' => $data['discount_percent'] ?? 0.00,
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        if (!$tierId) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to create tier'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Tier created successfully',
            'tier_id' => $tierId
        ]);
    }

    /**
     * Get tier for editing
     */
    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $tier = $this->tierModel->find($id);

        if (!$tier) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier not found'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'tier' => $tier
        ]);
    }

    /**
     * Update tier
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        // Validation
        if (empty($data['tier_name']) || empty($data['tier_slug'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier name and slug are required'
            ], 400);
        }

        // Check if slug exists (excluding current tier)
        if ($this->tierModel->slugExists($data['tier_slug'], $id)) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier slug already exists'
            ], 400);
        }

        // Update tier
        $updated = $this->tierModel->update($id, [
            'tier_name' => $data['tier_name'],
            'tier_slug' => $data['tier_slug'],
            'min_orders' => $data['min_orders'] ?? 0,
            'min_amount' => $data['min_amount'] ?? 0.00,
            'discount_percent' => $data['discount_percent'] ?? 0.00,
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'sort_order' => $data['sort_order'] ?? 0
        ]);

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to update tier'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Tier updated successfully'
        ]);
    }

    /**
     * Delete tier
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];

        // Check if tier exists
        $tier = $this->tierModel->find($id);
        if (!$tier) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier not found'
            ], 404);
        }

        // Prevent deleting if it's the standard tier
        if ($tier['tier_slug'] === 'standard') {
            return $this->json($response, [
                'success' => false,
                'message' => 'Cannot delete the standard tier'
            ], 400);
        }

        // Delete tier
        $deleted = $this->tierModel->delete($id);

        if (!$deleted) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to delete tier'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Tier deleted successfully'
        ]);
    }

    /**
     * Toggle tier status
     */
    public function toggleStatus(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $tier = $this->tierModel->find($id);

        if (!$tier) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Tier not found'
            ], 404);
        }

        $newStatus = $tier['is_active'] ? 0 : 1;
        $updated = $this->tierModel->update($id, ['is_active' => $newStatus]);

        if (!$updated) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Failed to toggle status'
            ], 500);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $newStatus
        ]);
    }
}
