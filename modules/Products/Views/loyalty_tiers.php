<style>
.tier-card {
    transition: transform 0.2s;
}
.tier-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.badge-discount {
    font-size: 1.1rem;
    padding: 0.5rem 1rem;
}
</style>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-award"></i> Loyalty Tiers Management</h2>
            <p class="text-muted">Manage customer loyalty tiers and discount levels</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" onclick="openTierModal()">
                <i class="bi bi-plus-circle"></i> Add New Tier
            </button>
            <a href="/products" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tier Name</th>
                            <th>Slug</th>
                            <th>Min Orders</th>
                            <th>Min Amount</th>
                            <th>Discount</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tiers)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">No loyalty tiers found. Add your first tier to get started.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tiers as $tier): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($tier['tier_name']) ?></strong>
                                        <?php if (!empty($tier['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($tier['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($tier['tier_slug']) ?></code></td>
                                    <td><?= $tier['min_orders'] ?> orders</td>
                                    <td>$<?= number_format($tier['min_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-success badge-discount">
                                            <?= number_format($tier['discount_percent'], 1) ?>% OFF
                                        </span>
                                    </td>
                                    <td><?= $tier['sort_order'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-<?= $tier['is_active'] ? 'success' : 'secondary' ?>"
                                                onclick="toggleTierStatus(<?= $tier['id'] ?>)">
                                            <i class="bi bi-<?= $tier['is_active'] ? 'check-circle' : 'x-circle' ?>"></i>
                                            <?= $tier['is_active'] ? 'Active' : 'Inactive' ?>
                                        </button>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="editTier(<?= $tier['id'] ?>)"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($tier['tier_slug'] !== 'standard'): ?>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteTier(<?= $tier['id'] ?>, '<?= htmlspecialchars($tier['tier_name']) ?>')"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="Cannot delete standard tier">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Tier Modal -->
<div class="modal fade" id="tierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tierModalTitle">Add New Tier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tierForm">
                <div class="modal-body">
                    <input type="hidden" id="tier_id" name="tier_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tier_name" class="form-label">Tier Name *</label>
                            <input type="text" class="form-control" id="tier_name" name="tier_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tier_slug" class="form-label">Slug *</label>
                            <input type="text" class="form-control" id="tier_slug" name="tier_slug" required
                                   pattern="[a-z0-9-]+" title="Only lowercase letters, numbers, and hyphens">
                            <small class="text-muted">Only lowercase letters, numbers, and hyphens</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="min_orders" class="form-label">Minimum Orders</label>
                            <input type="number" class="form-control" id="min_orders" name="min_orders"
                                   min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="min_amount" class="form-label">Minimum Amount ($)</label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount"
                                   min="0" step="0.01" value="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="discount_percent" class="form-label">Discount Percent (%)</label>
                            <input type="number" class="form-control" id="discount_percent" name="discount_percent"
                                   min="0" max="100" step="0.01" value="0.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order"
                                   min="0" value="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTierBtn">Save Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Open modal for new tier
    window.openTierModal = function() {
        $('#tierModalTitle').text('Add New Tier');
        $('#tierForm')[0].reset();
        $('#tier_id').val('');
        $('#is_active').prop('checked', true);
        $('#tierModal').modal('show');
    };

    // Edit tier
    window.editTier = function(tierId) {
        $.get(`/products/loyalty-tiers/${tierId}/edit`, function(response) {
            if (response.success) {
                const tier = response.tier;
                $('#tierModalTitle').text('Edit Tier');
                $('#tier_id').val(tier.id);
                $('#tier_name').val(tier.tier_name);
                $('#tier_slug').val(tier.tier_slug);
                $('#min_orders').val(tier.min_orders);
                $('#min_amount').val(tier.min_amount);
                $('#discount_percent').val(tier.discount_percent);
                $('#sort_order').val(tier.sort_order);
                $('#description').val(tier.description || '');
                $('#is_active').prop('checked', tier.is_active == 1);
                $('#tierModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Failed to load tier data');
        });
    };

    // Save tier (create or update)
    $('#tierForm').on('submit', function(e) {
        e.preventDefault();

        const tierId = $('#tier_id').val();
        const url = tierId ? `/products/loyalty-tiers/${tierId}/update` : '/products/loyalty-tiers';
        const formData = {
            tier_name: $('#tier_name').val(),
            tier_slug: $('#tier_slug').val(),
            min_orders: $('#min_orders').val(),
            min_amount: $('#min_amount').val(),
            discount_percent: $('#discount_percent').val(),
            sort_order: $('#sort_order').val(),
            description: $('#description').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };

        $('#saveTierBtn').prop('disabled', true).text('Saving...');

        $.post(url, formData)
            .done(function(response) {
                if (response.success) {
                    $('#tierModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Failed to save tier');
            })
            .always(function() {
                $('#saveTierBtn').prop('disabled', false).text('Save Tier');
            });
    });

    // Toggle tier status
    window.toggleTierStatus = function(tierId) {
        if (!confirm('Toggle this tier\'s status?')) return;

        $.post(`/products/loyalty-tiers/${tierId}/toggle`)
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Failed to toggle status');
            });
    };

    // Delete tier
    window.deleteTier = function(tierId, tierName) {
        if (!confirm(`Are you sure you want to delete "${tierName}"?\n\nThis action cannot be undone.`)) return;

        $.post(`/products/loyalty-tiers/${tierId}/delete`)
            .done(function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Failed to delete tier');
            });
    };

    // Auto-generate slug from tier name
    $('#tier_name').on('input', function() {
        if (!$('#tier_id').val()) { // Only for new tiers
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#tier_slug').val(slug);
        }
    });
});
</script>
