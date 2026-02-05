<?php
/**
 * Create Invoice Page
 * Create new invoice with multiple sale items
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create objects
$invoiceObj = new Invoice();
$productObj = new Product();

// Get all products for dropdown
$products = $productObj->getAllProducts();

$error = '';
$stockErrors = array();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerName = trim($_POST['customerName']);
    $productIDs = $_POST['productID'] ?? array();
    $quantities = $_POST['quantity'] ?? array();
    
    // Validate
    if (empty($customerName)) {
        $error = 'Customer name is required.';
    } elseif (empty($productIDs) || count($productIDs) == 0) {
        $error = 'Please add at least one product.';
    } else {
        // Prepare items array
        $items = array();
        $hasError = false;
        
        foreach ($productIDs as $index => $productID) {
            if (!empty($productID) && !empty($quantities[$index])) {
                $quantity = intval($quantities[$index]);
                
                // Check stock
                $product = $productObj->getProductByID($productID);
                if ($product['quantity'] < $quantity) {
                    $stockErrors[] = "{$product['name']}: Only {$product['quantity']} available";
                    $hasError = true;
                } else {
                    $items[] = array(
                        'productID' => $productID,
                        'quantity' => $quantity
                    );
                }
            }
        }
        
        if ($hasError) {
            $error = 'Insufficient stock for some products.';
        } elseif (empty($items)) {
            $error = 'No valid items to process.';
        } else {
            // Create invoice
            $userID = $_SESSION['userID'];
            $invoiceID = $invoiceObj->generateInvoice($userID, $customerName, $items);
            
            if ($invoiceID) {
                redirect(getBaseURL() . '/public/invoices/view.php?id=' . $invoiceID . '&success=created');
            } else {
                $error = 'Failed to create invoice. Please try again.';
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-success">
            <i class="bi bi-receipt"></i> Create New Invoice
        </h2>
        <p class="text-muted">Generate invoice with multiple products</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <?php if (!empty($stockErrors)): ?>
            <ul class="mb-0 mt-2">
                <?php foreach ($stockErrors as $stockError): ?>
                    <li><?= e($stockError) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="" id="invoiceForm">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-person"></i> Customer Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customerName" class="form-label">
                            Customer Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="customerName" 
                               name="customerName" 
                               value="<?= isset($_POST['customerName']) ? e($_POST['customerName']) : '' ?>"
                               required>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cart"></i> Sale Items
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        <!-- Item rows will be added here -->
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addItem()">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-calculator"></i> Invoice Summary
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items:</span>
                        <strong id="totalItems">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong id="subtotal">RM 0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-success" id="grandTotal">RM 0.00</strong>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle"></i> Generate Invoice
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
let itemCount = 0;
const products = <?= json_encode($products) ?>;

function addItem() {
    itemCount++;
    const container = document.getElementById('itemsContainer');
    const itemDiv = document.createElement('div');
    itemDiv.className = 'row mb-3 align-items-end item-row';
    itemDiv.id = 'item-' + itemCount;
    
    itemDiv.innerHTML = `
        <div class="col-md-6">
            <label class="form-label">Product</label>
            <select class="form-select" name="productID[]" onchange="updateSummary()" required>
                <option value="">-- Select Product --</option>
                ${products.map(p => `
                    <option value="${p.productID}" data-price="${p.price}" data-stock="${p.quantity}">
                        ${p.name} (${p.productCode}) - Stock: ${p.quantity}
                    </option>
                `).join('')}
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity[]" min="1" value="1" onchange="updateSummary()" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Price</label>
            <input type="text" class="form-control" readonly value="0.00">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${itemCount})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(itemDiv);
    updateSummary();
}

function removeItem(id) {
    const item = document.getElementById('item-' + id);
    if (item) {
        item.remove();
        updateSummary();
    }
}

function updateSummary() {
    const rows = document.querySelectorAll('.item-row');
    let total = 0;
    let items = 0;
    
    rows.forEach(row => {
        const select = row.querySelector('select');
        const quantity = parseInt(row.querySelector('input[name="quantity[]"]').value) || 0;
        const priceInput = row.querySelector('input[readonly]');
        
        if (select.value && quantity > 0) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.getAttribute('data-price')) || 0;
            const itemTotal = price * quantity;
            
            priceInput.value = itemTotal.toFixed(2);
            total += itemTotal;
            items++;
        } else {
            priceInput.value = '0.00';
        }
    });
    
    document.getElementById('totalItems').textContent = items;
    document.getElementById('subtotal').textContent = 'RM ' + total.toFixed(2);
    document.getElementById('grandTotal').textContent = 'RM ' + total.toFixed(2);
}

// Add first item on page load
window.onload = function() {
    addItem();
};
</script>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>