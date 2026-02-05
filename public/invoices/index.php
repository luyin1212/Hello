<?php
/**
 * Invoices List Page
 * Display all invoices
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Create Invoice object
$invoiceObj = new Invoice();

// Get all invoices
$invoices = $invoiceObj->getAllInvoices();

// Get success message
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-receipt"></i> Invoices
        </h2>
        <p class="text-muted">Manage sales invoices</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="create.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create New Invoice
        </a>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success == 'deleted'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Invoice deleted successfully! Inventory restored.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-table"></i> All Invoices
        <span class="badge bg-light text-dark float-end"><?= count($invoices) ?> invoices</span>
    </div>
    <div class="card-body">
        <?php if (count($invoices) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Issued By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td><strong>INV-<?= str_pad($inv['invoiceID'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= date('d M Y', strtotime($inv['invoiceDate'])) ?></td>
                                <td><?= e($inv['customerName']) ?></td>
                                <td><strong class="text-success"><?= formatCurrency($inv['totalAmount']) ?></strong></td>
                                <td><?= e($inv['username']) ?></td>
                                <td>
                                    <a href="view.php?id=<?= $inv['invoiceID'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $inv['invoiceID'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Delete this invoice? All sales will be removed and inventory restored.')"
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
                No invoices yet. Click "Create New Invoice" to get started.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>