<?php
/**
 * Alerts List Page
 * Display all low stock alerts
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Alert object
$alertObj = new Alert();

// Auto-check stock levels
$alertObj->checkStockLevel();

// Get all alerts
$alerts = $alertObj->getAllAlerts();

// Get success message
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-warning">
            <i class="bi bi-exclamation-triangle"></i> Low Stock Alerts
        </h2>
        <p class="text-muted">Products that need restocking</p>
    </div>
    <div class="col-md-4 text-end">
        <span class="badge bg-warning text-dark fs-5">
            <?= count($alerts) ?> Active Alerts
        </span>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success == 'cleared'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Alert cleared successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (count($alerts) > 0): ?>
    <!-- Alert Cards -->
    <div class="row">
        <?php foreach ($alerts as $alert): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        <strong><?= e($alert['name']) ?></strong>
                        <span class="badge bg-dark float-end"><?= e($alert['productCode']) ?></span>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?= e($alert['message']) ?></p>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">Current Stock:</small><br>
                                <strong class="text-danger fs-5"><?= $alert['quantity'] ?></strong> units
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Reorder Level:</small><br>
                                <strong class="fs-5"><?= $alert['reorderLevel'] ?></strong> units
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-2">
                            <small>
                                <i class="bi bi-info-circle"></i> 
                                Alert Date: <?= date('d M Y', strtotime($alert['alertDate'])) ?>
                            </small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="<?= getBaseURL() ?>/public/products/edit.php?id=<?= $alert['productID'] ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Restock Product
                            </a>
                            <a href="clear.php?id=<?= $alert['alertID'] ?>" 
                               class="btn btn-outline-secondary btn-sm"
                               onclick="return confirm('Clear this alert?')">
                                <i class="bi bi-x-circle"></i> Clear Alert
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> 
        <strong>All Clear!</strong> No low stock alerts at this time.
        All products have sufficient inventory.
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>