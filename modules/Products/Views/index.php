<style>
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-box-seam"></i> Products Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openProductModal()">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </button>
            </div>

            <!-- Filters -->
            <div class="card filter-card mb-4">
                <div class="card-body">
                    <form method="GET" action="/products" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="brand">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= htmlspecialchars($brand['brand']) ?>" <?= ($filters['brand'] ?? '') === $brand['brand'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($brand['brand']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= htmlspecialchars($category['category']) ?>" <?= ($filters['category'] ?? '') === $category['category'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['category']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-light w-100">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> No products found.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card product-card h-100">
                                <div class="product-image">
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-box-seam"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h6>
                                    <p class="card-text small text-muted mb-2">
                                        <strong>Brand:</strong> <?= htmlspecialchars($product['brand']) ?><br>
                                        <strong>Category:</strong> <?= htmlspecialchars($product['category']) ?><br>
                                        <strong>Style:</strong> <?= htmlspecialchars($product['style']) ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <div class="btn-group w-100" role="group">
                                        <a href="/products/<?= $product['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/products/<?= $product['id'] ?>/prices" class="btn btn-sm btn-success">
                                            <i class="bi bi-currency-dollar"></i>
                                        </a>
                                        <button class="btn btn-sm btn-warning" onclick="editProduct(<?= $product['id'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['totalPages'] > 1): ?>
                <nav aria-label="Products pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Style <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="style" name="style" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="brand" name="brand" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Save Product</button>
            </div>
        </div>
    </div>
</div>

<script>
    let editMode = false;

    function openProductModal() {
        editMode = false;
        $('#modalTitle').text('Add New Product');
        $('#productForm')[0].reset();
        $('#productId').val('');
    }

    function editProduct(id) {
        editMode = true;
        $('#modalTitle').text('Edit Product');

        $.get('/products/' + id + '/edit', function(response) {
            if (response.success) {
                const product = response.product;
                $('#productId').val(product.id);
                $('#product_name').val(product.product_name);
                $('#style').val(product.style);
                $('#brand').val(product.brand);
                $('#category').val(product.category);
                $('#image_url').val(product.image_url);
                $('#description').val(product.description);
                $('#productModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        });
    }

    function saveProduct() {
        const formData = {
            product_name: $('#product_name').val(),
            style: $('#style').val(),
            brand: $('#brand').val(),
            category: $('#category').val(),
            image_url: $('#image_url').val(),
            description: $('#description').val()
        };

        const url = editMode ? '/products/' + $('#productId').val() + '/update' : '/products';

        $.post(url, formData, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Failed to save product');
        });
    }

    function deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }

        $.post('/products/' + id + '/delete', {}, function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        }).fail(function() {
            alert('Failed to delete product');
        });
    }
</script>
