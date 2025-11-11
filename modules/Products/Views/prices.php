<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Management - <?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .editable-cell {
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .editable-cell:hover {
            background-color: #f8f9fa;
            box-shadow: 0 0 0 2px #0d6efd;
        }
        .editable-cell.editing {
            background-color: #fff3cd;
        }
        .editable-cell input {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
        }
        .price-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .tier-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .tier-standard { background: #6c757d; color: white; }
        .tier-silver { background: #c0c0c0; color: #333; }
        .tier-gold { background: #ffd700; color: #333; }
        .tier-platinum { background: #e5e4e2; color: #333; }
        .tier-diamond { background: #b9f2ff; color: #333; }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .null-value {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../../resources/views/layouts/app.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <a href="/products" class="btn btn-outline-secondary mb-2">
                            <i class="bi bi-arrow-left"></i> Back to Products
                        </a>
                        <h2><i class="bi bi-currency-dollar"></i> Price Management</h2>
                        <p class="text-muted mb-0">
                            <strong><?= htmlspecialchars($product['product_name']) ?></strong> -
                            <?= htmlspecialchars($product['brand']) ?> |
                            Style: <?= htmlspecialchars($product['style']) ?>
                        </p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#basePriceModal" onclick="openBasePriceModal()">
                        <i class="bi bi-plus-circle"></i> Add Size & Base Price
                    </button>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Quick Edit:</strong> Click on any price cell to edit inline.
                    Press Enter to save or Escape to cancel.
                    <strong>NULL values</strong> in loyalty prices will use base price.
                </div>

                <!-- Base Prices Table -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-table"></i> Base Prices (Standard for all users)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered price-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 80px;">Size</th>
                                        <th style="min-width: 100px;">Price</th>
                                        <th style="min-width: 110px;">Extra Price</th>
                                        <th style="min-width: 100px;">Rush Fee</th>
                                        <th style="min-width: 120px;">Shipping Fee</th>
                                        <th style="min-width: 140px;">Extra Ship Fee</th>
                                        <th style="min-width: 120px;">Priority Fee</th>
                                        <th style="min-width: 140px;">Extra Priority</th>
                                        <th style="min-width: 100px;">Label Fee</th>
                                        <th style="min-width: 110px;">Special Fee</th>
                                        <th style="min-width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($product['prices'])): ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">No prices configured. Add a size to start.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($product['prices'] as $price): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($price['size']) ?></strong></td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="price" data-type="base" data-value="<?= $price['price'] ?>">
                                                    $<?= number_format($price['price'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="extra_price" data-type="base" data-value="<?= $price['extra_price'] ?>">
                                                    $<?= number_format($price['extra_price'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="rush_fee" data-type="base" data-value="<?= $price['rush_fee'] ?>">
                                                    $<?= number_format($price['rush_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="shipping_fee" data-type="base" data-value="<?= $price['shipping_fee'] ?>">
                                                    $<?= number_format($price['shipping_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="extra_shipping_fee" data-type="base" data-value="<?= $price['extra_shipping_fee'] ?>">
                                                    $<?= number_format($price['extra_shipping_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="priority_fee" data-type="base" data-value="<?= $price['priority_fee'] ?>">
                                                    $<?= number_format($price['priority_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="extra_priority_fee" data-type="base" data-value="<?= $price['extra_priority_fee'] ?>">
                                                    $<?= number_format($price['extra_priority_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="label_fee" data-type="base" data-value="<?= $price['label_fee'] ?>">
                                                    $<?= number_format($price['label_fee'], 2) ?>
                                                </td>
                                                <td class="editable-cell" data-id="<?= $price['id'] ?>" data-field="special_fee" data-type="base" data-value="<?= $price['special_fee'] ?>">
                                                    $<?= number_format($price['special_fee'], 2) ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteBasePrice(<?= $price['id'] ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Loyalty Prices Tables -->
                <?php foreach ($tiers as $tier): ?>
                    <?php if ($tier['tier_slug'] === 'standard') continue; // Skip standard tier ?>
                    <div class="card mb-4">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <span class="tier-badge tier-<?= $tier['tier_slug'] ?>">
                                        <?= htmlspecialchars($tier['tier_name']) ?>
                                    </span>
                                    Tier Prices (<?= $tier['discount_percent'] ?>% discount)
                                </h5>
                                <button class="btn btn-sm btn-light" onclick="copyBasePricesToLoyalty(<?= $tier['id'] ?>)">
                                    <i class="bi bi-copy"></i> Copy from Base
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered price-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 80px;">Size</th>
                                            <th style="min-width: 100px;">Price</th>
                                            <th style="min-width: 110px;">Extra Price</th>
                                            <th style="min-width: 100px;">Rush Fee</th>
                                            <th style="min-width: 120px;">Shipping Fee</th>
                                            <th style="min-width: 140px;">Extra Ship Fee</th>
                                            <th style="min-width: 120px;">Priority Fee</th>
                                            <th style="min-width: 140px;">Extra Priority</th>
                                            <th style="min-width: 100px;">Label Fee</th>
                                            <th style="min-width: 110px;">Special Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get loyalty prices for this tier
                                        $tierPrices = [];
                                        foreach ($loyaltyPrices as $lp) {
                                            if ($lp['tier_id'] == $tier['id']) {
                                                foreach ($lp['prices'] as $p) {
                                                    $tierPrices[$p['size']] = $p;
                                                }
                                                break;
                                            }
                                        }
                                        ?>
                                        <?php if (empty($product['prices'])): ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">No base prices configured.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($product['prices'] as $basePrice): ?>
                                                <?php $loyaltyPrice = $tierPrices[$basePrice['size']] ?? null; ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($basePrice['size']) ?></strong></td>
                                                    <?php foreach (['price', 'extra_price', 'rush_fee', 'shipping_fee', 'extra_shipping_fee', 'priority_fee', 'extra_priority_fee', 'label_fee', 'special_fee'] as $field): ?>
                                                        <td class="editable-cell"
                                                            data-id="<?= $loyaltyPrice['id'] ?? 'new' ?>"
                                                            data-field="<?= $field ?>"
                                                            data-type="loyalty"
                                                            data-tier="<?= $tier['id'] ?>"
                                                            data-size="<?= $basePrice['size'] ?>"
                                                            data-value="<?= $loyaltyPrice[$field] ?? '' ?>">
                                                            <?php if ($loyaltyPrice && $loyaltyPrice[$field] !== null): ?>
                                                                $<?= number_format($loyaltyPrice[$field], 2) ?>
                                                            <?php else: ?>
                                                                <span class="null-value">NULL</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Base Price Modal -->
    <div class="modal fade" id="basePriceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Size & Base Price</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="basePriceForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Size <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="size" name="size" required placeholder="e.g., S, M, L, XL">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Extra Price</label>
                                <input type="number" step="0.01" class="form-control" id="extra_price" name="extra_price" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rush Fee</label>
                                <input type="number" step="0.01" class="form-control" id="rush_fee" name="rush_fee" value="1.49">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shipping Fee</label>
                                <input type="number" step="0.01" class="form-control" id="shipping_fee" name="shipping_fee" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Extra Shipping Fee</label>
                                <input type="number" step="0.01" class="form-control" id="extra_shipping_fee" name="extra_shipping_fee" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority Fee</label>
                                <input type="number" step="0.01" class="form-control" id="priority_fee" name="priority_fee" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Extra Priority Fee</label>
                                <input type="number" step="0.01" class="form-control" id="extra_priority_fee" name="extra_priority_fee" value="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Label Fee</label>
                                <input type="number" step="0.01" class="form-control" id="label_fee" name="label_fee" value="0.30">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Special Fee</label>
                                <input type="number" step="0.01" class="form-control" id="special_fee" name="special_fee" value="2.00">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveBasePrice()">Save Price</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const productId = <?= $product['id'] ?>;
        let currentCell = null;
        let originalValue = null;

        function openBasePriceModal() {
            $('#basePriceForm')[0].reset();
        }

        function saveBasePrice() {
            const formData = {
                size: $('#size').val(),
                price: $('#price').val(),
                extra_price: $('#extra_price').val(),
                rush_fee: $('#rush_fee').val(),
                shipping_fee: $('#shipping_fee').val(),
                extra_shipping_fee: $('#extra_shipping_fee').val(),
                priority_fee: $('#priority_fee').val(),
                extra_priority_fee: $('#extra_priority_fee').val(),
                label_fee: $('#label_fee').val(),
                special_fee: $('#special_fee').val()
            };

            $.post('/products/' + productId + '/prices', formData, function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }).fail(function() {
                alert('Failed to save price');
            });
        }

        function deleteBasePrice(priceId) {
            if (!confirm('Are you sure? This will also delete all loyalty prices for this size.')) {
                return;
            }

            $.post('/products/prices/' + priceId + '/delete', {}, function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }).fail(function() {
                alert('Failed to delete price');
            });
        }

        // Quick Edit Functionality
        $(document).on('click', '.editable-cell', function() {
            if (currentCell) return; // Already editing

            currentCell = $(this);
            originalValue = currentCell.data('value');
            const displayValue = originalValue === '' || originalValue === null ? '' : originalValue;

            currentCell.addClass('editing');
            const input = $('<input type="number" step="0.01" class="form-control form-control-sm">');
            input.val(displayValue);
            currentCell.html(input);
            input.focus().select();

            // Save on Enter
            input.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveQuickEdit();
                } else if (e.key === 'Escape') {
                    cancelQuickEdit();
                }
            });

            // Save on blur
            input.on('blur', function() {
                setTimeout(saveQuickEdit, 200);
            });
        });

        function saveQuickEdit() {
            if (!currentCell) return;

            const input = currentCell.find('input');
            const newValue = input.val();
            const id = currentCell.data('id');
            const field = currentCell.data('field');
            const type = currentCell.data('type');

            // If creating new loyalty price
            if (id === 'new' && type === 'loyalty') {
                const tierId = currentCell.data('tier');
                const size = currentCell.data('size');

                const data = {
                    tier_id: tierId,
                    size: size,
                    [field]: newValue === '' ? null : newValue
                };

                $.post('/products/' + productId + '/loyalty-prices', data, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        cancelQuickEdit();
                    }
                });
                return;
            }

            // Quick edit existing price
            const data = {
                id: id,
                field: field,
                type: type,
                value: newValue
            };

            $.post('/products/prices/quick-edit', data, function(response) {
                if (response.success) {
                    const displayValue = response.value === null ? '<span class="null-value">NULL</span>' : '$' + parseFloat(response.value).toFixed(2);
                    currentCell.html(displayValue);
                    currentCell.data('value', response.value);
                } else {
                    alert('Error: ' + response.message);
                    cancelQuickEdit();
                }
            }).fail(function() {
                alert('Failed to update price');
                cancelQuickEdit();
            }).always(function() {
                currentCell.removeClass('editing');
                currentCell = null;
            });
        }

        function cancelQuickEdit() {
            if (!currentCell) return;

            const displayValue = originalValue === '' || originalValue === null ? '<span class="null-value">NULL</span>' : '$' + parseFloat(originalValue).toFixed(2);
            currentCell.html(displayValue);
            currentCell.removeClass('editing');
            currentCell = null;
        }

        function copyBasePricesToLoyalty(tierId) {
            if (!confirm('Copy all base prices to this loyalty tier with automatic discount applied?')) {
                return;
            }

            // Get tier discount
            const tierDiscount = <?= json_encode(array_column($tiers, 'discount_percent', 'id')) ?>[tierId] || 0;

            alert('This feature will be implemented in the next update. For now, please manually set loyalty prices.');
        }
    </script>
</body>
</html>
