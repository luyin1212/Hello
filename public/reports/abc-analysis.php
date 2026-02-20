<?php
/**
 * ABC Analysis Report
 * Inventory classification using Pareto Principle
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Get ABC analysis data
$reportObj = new Report();
$products = $reportObj->getABCAnalysis();

// Count categories
$countA = 0;
$countB = 0;
$countC = 0;

foreach ($products as $product) {
    if ($product['category'] == 'A') $countA++;
    elseif ($product['category'] == 'B') $countB++;
    else $countC++;
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-graph-up-arrow"></i> ABC Analysis
        </h2>
        <p class="text-muted">Inventory Classification using Pareto Principle (80/20 Rule)</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= getBaseURL() ?>/public/reports/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Reports
        </a>
    </div>
</div>

<!-- Algorithm Explanation -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-info-circle"></i> About ABC Analysis
    </div>
    <div class="card-body">
        <p class="mb-2">
            <strong>ABC Analysis</strong> is an inventory categorization technique based on the 
            <strong>Pareto Principle</strong> (80/20 rule), which divides inventory into three categories:
        </p>
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <h5 class="text-danger">Category A</h5>
                        <p class="small mb-0">
                            <strong>High Priority</strong><br>
                            Items contributing to <strong>70%</strong> of total revenue<br>
                            Require tight control and accurate records
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="text-warning">Category B</h5>
                        <p class="small mb-0">
                            <strong>Medium Priority</strong><br>
                            Items contributing to next <strong>20%</strong> of revenue<br>
                            Moderate control required
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-secondary">
                    <div class="card-body">
                        <h5 class="text-secondary">Category C</h5>
                        <p class="small mb-0">
                            <strong>Low Priority</strong><br>
                            Items contributing to last <strong>10%</strong> of revenue<br>
                            Simple controls sufficient
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <p class="mt-3 mb-0 small">
            <strong>Reference:</strong> Pareto, V. (1896). <em>Cours d'économie politique.</em> Lausanne: F. Rouge.
        </p>
    </div>
</div>

<!-- Category Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h1 class="text-danger"><?= $countA ?></h1>
                <h5>Category A Products</h5>
                <p class="text-muted mb-0">High Priority Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h1 class="text-warning"><?= $countB ?></h1>
                <h5>Category B Products</h5>
                <p class="text-muted mb-0">Medium Priority Items</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <h1 class="text-secondary"><?= $countC ?></h1>
                <h5>Category C Products</h5>
                <p class="text-muted mb-0">Low Priority Items</p>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-table"></i> Product Classification Results
    </div>
    <div class="card-body">
        <?php if (count($products) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th class="text-end">Units Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">% of Total</th>
                            <th class="text-end">Cumulative %</th>
                            <th class="text-center">Category</th>
                            <th class="text-center">Priority</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($products as $product): 
                            // Determine row color based on category
                            $rowClass = '';
                            $badgeClass = '';
                            if ($product['category'] == 'A') {
                                $rowClass = 'table-danger';
                                $badgeClass = 'bg-danger';
                            } elseif ($product['category'] == 'B') {
                                $rowClass = 'table-warning';
                                $badgeClass = 'bg-warning text-dark';
                            } else {
                                $badgeClass = 'bg-secondary';
                            }
                        ?>
                            <tr class="<?= $rowClass ?>">
                                <td><strong><?= $rank ?></strong></td>
                                <td><?= e($product['productCode']) ?></td>
                                <td><?= e($product['name']) ?></td>
                                <td class="text-end"><?= $product['totalSold'] ?></td>
                                <td class="text-end"><?= formatCurrency($product['totalRevenue']) ?></td>
                                <td class="text-end"><?= $product['revenuePercentage'] ?>%</td>
                                <td class="text-end"><strong><?= $product['cumulativePercentage'] ?>%</strong></td>
                                <td class="text-center">
                                    <span class="badge <?= $badgeClass ?> fs-6">
                                        <?= $product['category'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small><?= $product['priority'] ?></small>
                                </td>
                            </tr>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No sales data available for ABC analysis.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Management Recommendations -->
<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-lightbulb"></i> Management Recommendations
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6 class="text-danger">
                    <i class="bi bi-star-fill"></i> Category A Strategy
                </h6>
                <ul class="small">
                    <li>Maintain tight inventory control</li>
                    <li>Frequent stock reviews</li>
                    <li>Accurate demand forecasting</li>
                    <li>Strong supplier relationships</li>
                    <li>Priority in warehouse placement</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-warning">
                    <i class="bi bi-star-half"></i> Category B Strategy
                </h6>
                <ul class="small">
                    <li>Moderate control measures</li>
                    <li>Regular but less frequent reviews</li>
                    <li>Standard inventory procedures</li>
                    <li>Balance between cost and availability</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-secondary">
                    <i class="bi bi-star"></i> Category C Strategy
                </h6>
                <ul class="small">
                    <li>Simple inventory controls</li>
                    <li>Periodic reviews</li>
                    <li>Bulk ordering to reduce costs</li>
                    <li>Minimal safety stock</li>
                    <li>Consider discontinuing slow movers</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>