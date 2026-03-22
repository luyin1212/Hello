<?php
/**
 * Edit Product Page
 * Form to edit existing product details
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Product object
$productObj = new Product();

// Get product ID from URL
$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productID) {
    redirect(getBaseURL() . '/public/products/index.php?error=Invalid product ID');
}

// Get product details
$product = $productObj->getProductByID($productID);

if (!$product) {
    redirect(getBaseURL() . '/public/products/index.php?error=Product not found');
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productCode = trim($_POST['productCode']);
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $reorderLevel = intval($_POST['reorderLevel']);
    
    // Validate
    if (empty($productCode) || empty($name)) {
        $error = 'Product code and name are required.';
    } elseif ($price < 0) {
        $error = 'Price cannot be negative.';
    } elseif ($quantity < 0) {
        $error = 'Quantity cannot be negative.';
    } else {
        // Try to update product
        if ($productObj->updateProduct($productID, $productCode, $name, $category, $price, $quantity, $reorderLevel)) {
            redirect(getBaseURL() . '/public/products/index.php?success=updated');
        } else {
            $error = 'Failed to update product. Product code may already exist.';
        }
    }
    
    // If validation failed, keep form values
    if ($error) {
        $product['productCode'] = $productCode;
        $product['name'] = $name;
        $product['category'] = $category;
        $product['price'] = $price;
        $product['quantity'] = $quantity;
        $product['reorderLevel'] = $reorderLevel;
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-warning">
            <i class="bi bi-pencil"></i> Edit Product
        </h2>
        <p class="text-muted">Update product information</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-box"></i> Product Information
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="productCode" class="form-label">
                                Product Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="productCode" 
                                   name="productCode" 
                                   value="<?= e($product['productCode']) ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="name" class="form-label">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= e($product['name']) ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" 
                               class="form-control" 
                               id="category" 
                               name="category" 
                               value="<?= e($product['category']) ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">
                                Price (RM) <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="price" 
                                   name="price" 
                                   step="0.01" 
                                   min="0"
                                   value="<?= e($product['price']) ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">
                                Current Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="quantity" 
                                   min="0"
                                   value="<?= e($product['quantity']) ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="reorderLevel" class="form-label">
                                Reorder Level
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="reorderLevel" 
                                   name="reorderLevel" 
                                   min="0"
                                   value="<?= e($product['reorderLevel']) ?>">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Product
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-clock-history"></i> Product Status
            </div>
            <div class="card-body">
                <p><strong>Product ID:</strong> <?= $product['productID'] ?></p>
                <p><strong>Current Stock:</strong> 
                    <?php if ($product['quantity'] <= 0): ?>
                        <span class="badge bg-danger">Out of Stock</span>
                    <?php elseif ($product['quantity'] <= $product['reorderLevel']): ?>
                        <span class="badge bg-warning">Low Stock</span>
                    <?php else: ?>
                        <span class="badge bg-success">In Stock</span>
                    <?php endif; ?>
                </p>
                <p class="mb-0"><strong>Stock Value:</strong> <?= formatCurrency($product['price'] * $product['quantity']) ?></p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>