<?php
/**
 * System Settings Page
 * Display and manage system configuration
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Get system info
$productObj = new Product();
$invoiceObj = new Invoice();
$alertObj = new Alert();
$userObj = new User();

$totalProducts = $productObj->getTotalProducts();
$totalInvoices = $invoiceObj->getTotalInvoices();
$totalAlerts = $alertObj->getTotalAlerts();

// Get database info
$db = Database::getInstance();
$conn = $db->getConnection();
$dbSize = 0;

// Get database size
$result = $conn->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size 
                        FROM information_schema.TABLES 
                        WHERE table_schema = 'smart_inventory_v2'");
if ($result) {
    $row = $result->fetch_assoc();
    $dbSize = round($row['size'], 2);
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-gear"></i> System Settings
        </h2>
        <p class="text-muted">View and manage system configuration</p>
    </div>
</div>

<div class="row">
    <!-- System Information -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> System Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Application Name:</th>
                        <td><?= APP_NAME ?></td>
                    </tr>
                    <tr>
                        <th>Version:</th>
                        <td><?= APP_VERSION ?></td>
                    </tr>
                    <tr>
                        <th>PHP Version:</th>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <th>Server:</th>
                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
                    </tr>
                    <tr>
                        <th>Database:</th>
                        <td>smart_inventory_v2</td>
                    </tr>
                    <tr>
                        <th>Database Size:</th>
                        <td><?= $dbSize ?> MB</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Database Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-database"></i> Database Statistics
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Total Users:</th>
                        <td>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as count FROM user");
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Products:</th>
                        <td><?= $totalProducts ?></td>
                    </tr>
                    <tr>
                        <th>Total Invoices:</th>
                        <td><?= $totalInvoices ?></td>
                    </tr>
                    <tr>
                        <th>Total Sales:</th>
                        <td>
                            <?php
                            $result = $conn->query("SELECT COUNT(*) as count FROM sale");
                            $row = $result->fetch_assoc();
                            echo $row['count'];
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Active Alerts:</th>
                        <td>
                            <span class="badge bg-warning"><?= $totalAlerts ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Revenue:</th>
                        <td>
                            <?php
                            $result = $conn->query("SELECT SUM(totalAmount) as total FROM invoice");
                            $row = $result->fetch_assoc();
                            echo formatCurrency($row['total'] ?? 0);
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-check-circle"></i> System Status
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <i class="bi bi-database-check text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">Database</h6>
                        <span class="badge bg-success">Connected</span>
                    </div>
                    
                    <div class="col-md-3 text-center mb-3">
                        <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">Authentication</h6>
                        <span class="badge bg-success">Active</span>
                    </div>
                    
                    <div class="col-md-3 text-center mb-3">
                        <i class="bi bi-server text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">Server</h6>
                        <span class="badge bg-success">Running</span>
                    </div>
                    
                    <div class="col-md-3 text-center mb-3">
                        <i class="bi bi-activity text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">System</h6>
                        <span class="badge bg-success">Operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modules Status -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-grid"></i> Installed Modules
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Version</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-person"></i> User Management</td>
                                <td>User authentication and authorization</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-box"></i> Product Management</td>
                                <td>Product inventory management</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-receipt"></i> Invoice System</td>
                                <td>Invoice and sales management</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-exclamation-triangle"></i> Alert System</td>
                                <td>Low stock alerts and notifications</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-graph-up"></i> Reporting</td>
                                <td>Sales reports and analytics</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>1.0.0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>