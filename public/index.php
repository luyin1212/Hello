<?php
/**
 * Dashboard - Main Application Page
 * Smart Inventory Management System
 */

require_once __DIR__ . '/../config/config.php';

// Include header (this will check authentication)
include_once __DIR__ . '/../includes/header.php';

// Create objects for statistics
$productObj = new Product();
$invoiceObj = new Invoice();
$alertObj = new Alert();

// Get statistics
$totalProducts = $productObj->getTotalProducts();
$totalInvoices = $invoiceObj->getTotalInvoices();
$totalAlerts = $alertObj->getTotalAlerts();

// Get current user info
$currentUser = new User();
$username = $currentUser->getUsername();
$role = $currentUser->getRole();

// Get database instance
$db = Database::getInstance();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h2>
        <p class="text-muted">Welcome back, <?= e($username) ?>!</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Users -->
    <div class="col-md-3 mb-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-people-fill text-primary" style="font-size: 2.5rem;"></i>
                <h5 class="card-title mt-3">Total Users</h5>
                <?php
                    $result = $db->query("SELECT COUNT(*) as count FROM user");
                    $row = $result->fetch_assoc();
                    $totalUsers = $row['count'];
                ?>
                <h2 class="text-primary mb-0"><?= $totalUsers ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Total Products -->
    <div class="col-md-3 mb-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-box-seam-fill text-success" style="font-size: 2.5rem;"></i>
                <h5 class="card-title mt-3">Total Products</h5>
                <?php
                    $result = $db->query("SELECT COUNT(*) as count FROM product");
                    $row = $result->fetch_assoc();
                    $totalProducts = $row['count'];
                ?>
                <h2 class="text-success mb-0"><?= $totalProducts ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Total Invoices -->
    <div class="col-md-3 mb-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-receipt-cutoff text-info" style="font-size: 2.5rem;"></i>
                <h5 class="card-title mt-3">Total Invoices</h5>
                <?php
                    $result = $db->query("SELECT COUNT(*) as count FROM invoice");
                    $row = $result->fetch_assoc();
                    $totalInvoices = $row['count'];
                ?>
                <h2 class="text-info mb-0"><?= $totalInvoices ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Active Alerts -->
    <div class="col-md-3 mb-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 2.5rem;"></i>
                <h5 class="card-title mt-3">Active Alerts</h5>
                <?php
                    $result = $db->query("SELECT COUNT(*) as count FROM alert");
                    $row = $result->fetch_assoc();
                    $totalAlerts = $row['count'];
                ?>
                <h2 class="text-warning mb-0"><?= $totalAlerts ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> System Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Application:</strong> <?= APP_NAME ?></p>
                        <p><strong>Version:</strong> <?= APP_VERSION ?></p>
                        <p><strong>Your Role:</strong> <span class="badge bg-primary"><?= e($role) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Database:</strong> smart_inventory_v2</p>
                        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
                        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-check-circle"></i> System Status
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        Database Connected
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        Authentication Active
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle-fill text-success"></i> 
                        System Operational
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-lightning-fill"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="<?= getBaseURL() ?>/public/products/add.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-plus-circle"></i><br>
                            Add Product
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= getBaseURL() ?>/public/invoices/create.php" class="btn btn-outline-success btn-lg w-100 disabled">
                            <i class="bi bi-receipt"></i><br>
                            Create Invoice
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-info btn-lg w-100 disabled">
                            <i class="bi bi-bar-chart"></i><br>
                            View Reports
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="#" class="btn btn-outline-warning btn-lg w-100 disabled">
                            <i class="bi bi-bell"></i><br>
                            Check Alerts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>