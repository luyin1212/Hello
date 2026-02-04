<?php
/**
 * Sales Records List Page
 * Displays all sales transactions
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Sale object
$sale = new Sale();

// Get date filter if provided
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Get sales records
if ($startDate && $endDate) {
    $sales = $sale->getSalesByDateRange($startDate, $endDate);
    $totalAmount = $sale->getTotalSalesAmount($startDate, $endDate);
} else {
    $sales = $sale->getAllSales(100); // Limit to last 100 sales
    $totalAmount = 0;
    foreach ($sales as $s) {
        $totalAmount += $s['totalPrice'];
    }
}

// Get success message
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-cart-check"></i> Sales Records
        </h2>
        <p class="text-muted">View and manage sales transactions</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="add.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Record New Sale
        </a>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success == 'added'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Sale recorded successfully! Inventory updated.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($success == 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Sale deleted and inventory restored.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Sales Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-cart-check-fill text-primary" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Total Sales</h5>
                <h3 class="text-primary mb-0"><?= count($sales) ?></h3>
                <p class="text-muted small mb-0">Transactions</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Total Revenue</h5>
                <h3 class="text-success mb-0"><?= formatCurrency($totalAmount) ?></h3>
                <p class="text-muted small mb-0">
                    <?= $startDate && $endDate ? "For selected period" : "Last 100 transactions" ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Average Sale</h5>
                <h3 class="text-info mb-0">
                    <?= count($sales) > 0 ? formatCurrency($totalAmount / count($sales)) : formatCurrency(0) ?>
                </h3>
                <p class="text-muted small mb-0">Per transaction</p>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" 
                       class="form-control" 
                       id="start_date" 
                       name="start_date" 
                       value="<?= e($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" 
                       class="form-control" 
                       id="end_date" 
                       name="end_date" 
                       value="<?= e($endDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sales Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-table"></i> Sales Transactions
        <span class="badge bg-light text-dark float-end"><?= count($sales) ?> records</span>
    </div>
    <div class="card-body">
        <?php if (count($sales) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Product Code</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Invoice</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td><strong><?= $s['saleID'] ?></strong></td>
                                <td><?= date('d M Y', strtotime($s['saleDate'])) ?></td>
                                <td><?= e($s['productName'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-secondary"><?= e($s['productCode'] ?? 'N/A') ?></span></td>
                                <td><strong><?= $s['quantitySold'] ?></strong></td>
                                <td><strong><?= formatCurrency($s['totalPrice']) ?></strong></td>
                                <td>
                                    <?php if ($s['invoiceID']): ?>
                                        <span class="badge bg-info">INV-<?= $s['invoiceID'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="delete.php?id=<?= $s['saleID'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this sale? Inventory will be restored.')"
                                       title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> 
                <?php if ($startDate && $endDate): ?>
                    No sales found for the selected date range.
                <?php else: ?>
                    No sales records yet. Click "Record New Sale" to get started.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>