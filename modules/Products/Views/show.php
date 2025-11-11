<style>
    .product-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 5rem;
        border-radius: 8px;
    }
    .color-swatch {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: inline-block;
        border: 3px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .color-swatch:hover {
        transform: scale(1.1);
        border-color: #495057;
    }
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .price-badge {
        background: white;
        color: #667eea;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: bold;
        display: inline-block;
        margin: 5px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <a href="/products" class="btn btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-box-seam"></i> Product Details</h2>
                    <div>
                        <a href="/products/<?= $product['id'] ?>/prices" class="btn btn-success">
                            <i class="bi bi-currency-dollar"></i> Manage Prices
                        </a>
                        <button class="btn btn-warning" onclick="editProduct()">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Product Image -->
                <div class="col-md-4">
                    <div class="product-image mb-4">
                        <?php if ($product['image_url']): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-box-seam"></i>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="col-md-8">
                    <div class="info-card">
                        <h3 class="mb-3"><?= htmlspecialchars($product['product_name']) ?></h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="bi bi-tag"></i> Brand:</strong> <?= htmlspecialchars($product['brand']) ?></p>
                                <p class="mb-2"><strong><i class="bi bi-folder"></i> Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                                <p class="mb-2"><strong><i class="bi bi-upc"></i> Style:</strong> <?= htmlspecialchars($product['style']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong><i class="bi bi-calendar"></i> Created:</strong> <?= date('M d, Y', strtotime($product['created_at'])) ?></p>
                                <p class="mb-2"><strong><i class="bi bi-clock"></i> Updated:</strong> <?= date('M d, Y', strtotime($product['updated_at'])) ?></p>
                                <p class="mb-2">
                                    <strong><i class="bi bi-circle-fill"></i> Status:</strong>
                                    <span class="badge bg-success">Active</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <?php if ($product['description']): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-file-text"></i> Description</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colors -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Available Colors (<?= count($product['colors']) ?>)</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#colorModal" onclick="openColorModal()">
                        <i class="bi bi-plus-circle"></i> Add Color
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($product['colors'])): ?>
                        <p class="text-muted mb-0">No colors configured yet.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($product['colors'] as $color): ?>
                                <div class="col-md-2 col-sm-4 col-6 text-center mb-3">
                                    <div class="color-swatch mx-auto" style="background-color: <?= htmlspecialchars($color['color_code']) ?>" title="<?= htmlspecialchars($color['color_name']) ?>"></div>
                                    <p class="mb-1 mt-2"><strong><?= htmlspecialchars($color['color_name']) ?></strong></p>
                                    <small class="text-muted"><?= htmlspecialchars($color['color_code']) ?></small>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-warning" onclick="editColor(<?= $color['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteColor(<?= $color['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Prices Summary -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Price Summary (<?= count($product['prices']) ?> sizes)</h5>
                    <a href="/products/<?= $product['id'] ?>/prices" class="btn btn-sm btn-success">
                        <i class="bi bi-gear"></i> Manage Prices
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($product['prices'])): ?>
                        <p class="text-muted mb-0">No prices configured yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Size</th>
                                        <th>Base Price</th>
                                        <th>Extra Price</th>
                                        <th>Rush Fee</th>
                                        <th>Shipping Fee</th>
                                        <th>Total (Estimate)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product['prices'] as $price): ?>
                                        <?php
                                        $total = $price['price'] + $price['extra_price'] + $price['rush_fee'] +
                                               $price['shipping_fee'] + $price['priority_fee'] +
                                               $price['label_fee'] + $price['special_fee'];
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($price['size']) ?></strong></td>
                                            <td>$<?= number_format($price['price'], 2) ?></td>
                                            <td>$<?= number_format($price['extra_price'], 2) ?></td>
                                            <td>$<?= number_format($price['rush_fee'], 2) ?></td>
                                            <td>$<?= number_format($price['shipping_fee'], 2) ?></td>
                                            <td><strong class="text-success">$<?= number_format($total, 2) ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Color Modal -->
<div class="modal fade" id="colorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorModalTitle">Add Color</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="colorForm">
                    <input type="hidden" id="colorId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Color Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="color_name" name="color_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="color_code" name="color_code" required>
                            <input type="text" class="form-control" id="color_code_text" placeholder="#000000">
                        </div>
                        <small class="form-text text-muted">Choose a color or enter hex code</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Image URL</label>
                        <input type="url" class="form-control" id="color_image" name="color_image">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveColor()">Save Color</button>
            </div>
        </div>
    </div>
</div>

<script>
    const productId = <?= $product['id'] ?>;
    let editColorMode = false;

    // Sync color picker and text input
    $('#color_code').on('input', function() {
        $('#color_code_text').val($(this).val());
    });
    $('#color_code_text').on('input', function() {
        $('#color_code').val($(this).val());
    });

    function openColorModal() {
        editColorMode = false;
        $('#colorModalTitle').text('Add Color');
        $('#colorForm')[0].reset();
        $('#colorId').val('');
    }

    function editColor(id) {
        editColorMode = true;
        $('#colorModalTitle').text('Edit Color');

        // Get color data
        const colors = <?= json_encode($product['colors']) ?>;
        const color = colors.find(c => c.id == id);

        if (color) {
            $('#colorId').val(color.id);
            $('#color_name').val(color.color_name);
            $('#color_code').val(color.color_code);
            $('#color_code_text').val(color.color_code);
            $('#color_image').val(color.color_image);
            $('#colorModal').modal('show');
        }
    }

    function saveColor() {
        const formData = {
            color_name: $('#color_name').val(),
            color_code: $('#color_code').val(),
            color_image: $('#color_image').val()
        };

        const url = editColorMode ?
            '/products/colors/' + $('#colorId').val() + '/update' :
            '/products/' + productId + '/colors';

        $.post(url, formData, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Failed to save color');
        });
    }

    function deleteColor(id) {
        if (!confirm('Are you sure you want to delete this color?')) {
            return;
        }

        $.post('/products/colors/' + id + '/delete', {}, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Failed to delete color');
        });
    }

    function editProduct() {
        window.location.href = '/products?edit=' + productId;
    }
</script>
