<?php
/**
 * Add Product Page
 * Form to add new products to inventory
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Product object
$productObj = new Product();

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
        // Try to add product
        if ($productObj->addProduct($productCode, $name, $category, $price, $quantity, $reorderLevel)) {
            redirect(getBaseURL() . '/public/products/index.php?success=added');
        } else {
            $error = 'Failed to add product. Product code may already exist.';
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-success">
            <i class="bi bi-plus-circle"></i> Add New Product
        </h2>
        <p class="text-muted">Add a new product to your inventory</p>
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
            <div class="card-header bg-success text-white">
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
                                   value="<?= isset($_POST['productCode']) ? e($_POST['productCode']) : '' ?>"
                                   required>
                            <div class="form-text">Unique identifier for the product</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="name" class="form-label">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" 
                               class="form-control" 
                               id="category" 
                               name="category" 
                               value="<?= isset($_POST['category']) ? e($_POST['category']) : '' ?>"
                               placeholder="e.g., Electronics, Furniture, Stationery">
                        <div class="form-text">Product category for organization</div>
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
                                   value="<?= isset($_POST['price']) ? e($_POST['price']) : '0.00' ?>"
                                   required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">
                                Initial Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="quantity" 
                                   name="quantity" 
                                   min="0"
                                   value="<?= isset($_POST['quantity']) ? e($_POST['quantity']) : '0' ?>"
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
                                   value="<?= isset($_POST['reorderLevel']) ? e($_POST['reorderLevel']) : '10' ?>">
                            <div class="form-text">Alert when stock falls below this</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Add Product
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
            <div class="card-header bg-info text-white">
                <i class="bi bi-info-circle"></i> Guidelines
            </div>
            <div class="card-body">
                <h6>Product Code</h6>
                <p class="small">Must be unique. Use a consistent format like SKU-001, PROD-001, etc.</p>
                
                <h6>Reorder Level</h6>
                <p class="small">Set the minimum quantity before the system alerts you to restock.</p>
                
                <h6>Category</h6>
                <p class="small mb-0">Helps in organizing and searching products. Be consistent with naming.</p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>