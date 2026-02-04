<?php
/**
 * Add Sale Page
 * Form to record new sales transaction
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create objects
$saleObj = new Sale();
$productObj = new Product();

// Get all products for dropdown
$products = $productObj->getAllProducts();

$error = '';
$productError = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productID = intval($_POST['productID']);
    $quantitySold = intval($_POST['quantitySold']);
    
    // Validate
    if (empty($productID)) {
        $error = 'Please select a product.';
    } elseif ($quantitySold <= 0) {
        $error = 'Quantity must be greater than 0.';
    } else {
        // Check if enough stock
        $product = $productObj->getProductByID($productID);
        
        if (!$product) {
            $error = 'Product not found.';
        } elseif ($product['quantity'] < $quantitySold) {
            $productError = "Insufficient stock! Available: {$product['quantity']} units";
            $error = 'Insufficient stock for this product.';
        } else {
            // Try to record sale
            $saleID = $saleObj->recordSale($productID, $quantitySold);
            
            if ($saleID) {
                redirect(getBaseURL() . '/public/sales/index.php?success=added');
            } else {
                $error = 'Failed to record sale. Please try again.';
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-success">
            <i class="bi bi-cart-plus"></i> Record New Sale
        </h2>
        <p class="text-muted">Record a sales transaction and update inventory</p>
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
                <i class="bi bi-cart-check"></i> Sale Information
            </div>
            <div class="card-body">
                <form method="POST" action="" id="saleForm">
                    <div class="mb-3">
                        <label for="productID" class="form-label">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" 
                                id="productID" 
                                name="productID" 
                                required
                                onchange="updateProductInfo()">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['productID'] ?>" 
                                        data-price="<?= $p['price'] ?>"
                                        data-stock="<?= $p['quantity'] ?>"
                                        data-code="<?= e($p['productCode']) ?>"
                                        <?= (isset($_POST['productID']) && $_POST['productID'] == $p['productID']) ? 'selected' : '' ?>>
                                    <?= e($p['name']) ?> (<?= e($p['productCode']) ?>) - Stock: <?= $p['quantity'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Product Info Display -->
                    <div id="productInfo" class="alert alert-info d-none mb-3">
                        <strong>Product Details:</strong><br>
                        Code: <span id="displayCode">-</span><br>
                        Price: RM <span id="displayPrice">0.00</span><br>
                        Available Stock: <span id="displayStock">0</span> units
                    </div>
                    
                    <?php if ($productError): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <?= e($productError) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="quantitySold" class="form-label">
                            Quantity <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="quantitySold" 
                               name="quantitySold" 
                               min="1"
                               value="<?= isset($_POST['quantitySold']) ? e($_POST['quantitySold']) : '1' ?>"
                               required
                               oninput="calculateTotal()">
                        <div class="form-text">Enter the quantity sold</div>
                    </div>
                    
                    <!-- Total Price Display -->
                    <div class="mb-3">
                        <label class="form-label">Total Price</label>
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="text" 
                                   class="form-control" 
                                   id="totalPrice" 
                                   value="0.00" 
                                   readonly>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Record Sale
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
                <i class="bi bi-info-circle"></i> Information
            </div>
            <div class="card-body">
                <h6>Recording a Sale</h6>
                <p class="small">When you record a sale:</p>
                <ul class="small">
                    <li>Product quantity will be automatically reduced</li>
                    <li>Sale will be recorded with current date</li>
                    <li>Total price will be calculated automatically</li>
                </ul>
                
                <h6 class="mt-3">Important</h6>
                <p class="small mb-0">Make sure the product has sufficient stock before recording the sale.</p>
            </div>
        </div>
    </div>
</div>

<script>
function updateProductInfo() {
    const select = document.getElementById('productID');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('displayCode').textContent = option.getAttribute('data-code');
        document.getElementById('displayPrice').textContent = parseFloat(option.getAttribute('data-price')).toFixed(2);
        document.getElementById('displayStock').textContent = option.getAttribute('data-stock');
        document.getElementById('productInfo').classList.remove('d-none');
        calculateTotal();
    } else {
        document.getElementById('productInfo').classList.add('d-none');
        document.getElementById('totalPrice').value = '0.00';
    }
}

function calculateTotal() {
    const select = document.getElementById('productID');
    const option = select.options[select.selectedIndex];
    const quantity = parseInt(document.getElementById('quantitySold').value) || 0;
    
    if (option.value && quantity > 0) {
        const price = parseFloat(option.getAttribute('data-price'));
        const total = price * quantity;
        document.getElementById('totalPrice').value = total.toFixed(2);
    } else {
        document.getElementById('totalPrice').value = '0.00';
    }
}
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>