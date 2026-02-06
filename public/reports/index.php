<?php
/**
 * Reports Page
 * Generate and view sales reports
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Report object
$reportObj = new Report();

// Default date range (last 30 days)
$endDate = date('Y-m-d');
$startDate = date('Y-m-d', strtotime('-30 days'));

// Get dates from form if submitted
if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
}

// Generate report
$report = $reportObj->generateReport($startDate, $endDate);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-graph-up"></i> Sales Reports
        </h2>
        <p class="text-muted">View sales analytics and performance</p>
    </div>
</div>

<!-- Date Range Selector -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" 
                       class="form-control" 
                       id="startDate" 
                       name="startDate" 
                       value="<?= e($startDate) ?>"
                       required>
            </div>
            <div class="col-md-4">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" 
                       class="form-control" 
                       id="endDate" 
                       name="endDate" 
                       value="<?= e($endDate) ?>"
                       required>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bi bi-receipt text-primary" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Total Invoices</h5>
                <h2 class="text-primary"><?= $report['totalInvoices'] ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-currency-dollar text-success" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Total Revenue</h5>
                <h2 class="text-success"><?= formatCurrency($report['totalRevenue']) ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-box text-info" style="font-size: 2rem;"></i>
                <h5 class="card-title mt-2">Items Sold</h5>
                <h2 class="text-info"><?= $report['totalItemsSold'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-trophy"></i> Top Selling Products
    </div>
    <div class="card-body">
        <?php if (count($report['topProducts']) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Quantity Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($report['topProducts'] as $product): ?>
                            <tr>
                                <td>
                                    <?php if ($rank == 1): ?>
                                        <i class="bi bi-trophy-fill text-warning"></i>
                                    <?php else: ?>
                                        <?= $rank ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($product['productCode']) ?></td>
                                <td><strong><?= e($product['name']) ?></strong></td>
                                <td><?= $product['totalSold'] ?></td>
                                <td><strong class="text-success"><?= formatCurrency($product['totalRevenue']) ?></strong></td>
                            </tr>
                        <?php $rank++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> No sales data for this period.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Daily Sales Chart -->
<?php if (count($report['dailySales']) > 0): ?>
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-bar-chart"></i> Daily Sales Trend
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoices</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['dailySales'] as $day): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($day['invoiceDate'])) ?></td>
                            <td><?= $day['invoiceCount'] ?></td>
                            <td><?= formatCurrency($day['dailyRevenue']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Low Stock Alert -->
<?php if (count($report['lowStockProducts']) > 0): ?>
<div class="card border-warning">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-exclamation-triangle"></i> Low Stock Products
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Name</th>
                        <th>Current Stock</th>
                        <th>Reorder Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['lowStockProducts'] as $product): ?>
                        <tr>
                            <td><?= e($product['productCode']) ?></td>
                            <td><?= e($product['name']) ?></td>
                            <td><strong class="text-danger"><?= $product['quantity'] ?></strong></td>
                            <td><?= $product['reorderLevel'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>