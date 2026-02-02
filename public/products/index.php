<?php
/**
 * Products List Page
 * Displays all products with search functionality
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Product object
$product = new Product();

// Handle search
$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = trim($_GET['search']);
    $products = $product->searchProduct($searchKeyword);
} else {
    $products = $product->getAllProducts();
}

// Get success message from URL
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-box-seam"></i> Product Management
        </h2>
        <p class="text-muted">Manage your inventory products</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="add.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New Product
        </a>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success == 'added'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Product added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-10">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by product name, code, or category..." 
                       value="<?= e($searchKeyword) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
        <?php if ($searchKeyword): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary mt-2">
                <i class="bi bi-x-circle"></i> Clear Search
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-table"></i> Products List
        <span class="badge bg-light text-dark float-end"><?= count($products) ?> products</span>
    </div>
    <div class="card-body">
        <?php if (count($products) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Product Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><strong><?= e($p['productCode']) ?></strong></td>
                                <td><?= e($p['name']) ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= e($p['category'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td><?= formatCurrency($p['price']) ?></td>
                                <td>
                                    <strong><?= $p['quantity'] ?></strong>
                                </td>
                                <td><?= $p['reorderLevel'] ?></td>
                                <td>
                                    <?php if ($p['quantity'] <= 0): ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php elseif ($p['quantity'] <= $p['reorderLevel']): ?>
                                        <span class="badge bg-warning">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Edit/Delete tomorrow</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> 
                <?php if ($searchKeyword): ?>
                    No products found matching "<?= e($searchKeyword) ?>".
                <?php else: ?>
                    No products in inventory yet. Click "Add New Product" to get started.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>