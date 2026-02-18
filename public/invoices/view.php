<?php
/**
 * View Invoice Details
 * Display complete invoice with all sale items
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Get invoice ID from URL
$invoiceID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$invoiceID) {
    redirect(getBaseURL() . '/public/invoices/index.php?error=Invalid invoice ID');
}

// Get invoice details
$invoiceObj = new Invoice();
$invoice = $invoiceObj->getInvoiceByID($invoiceID);

if (!$invoice) {
    redirect(getBaseURL() . '/public/invoices/index.php?error=Invoice not found');
}

// Get success message
$success = $_GET['success'] ?? '';
?>

<?php if ($success == 'created'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> Invoice created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2 class="text-primary">
            <i class="bi bi-receipt"></i> Invoice Details
        </h2>
    </div>
    <div class="col-md-4 text-end">
        <button onclick="window.print()" class="btn btn-success">
            <i class="bi bi-printer"></i> Print Invoice
        </button>
        <a href="<?= getBaseURL() ?>/public/invoices/index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<!-- Invoice Card -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-0">
                    <i class="bi bi-file-text"></i> Invoice #<?= str_pad($invoice['invoiceID'], 4, '0', STR_PAD_LEFT) ?>
                </h4>
            </div>
            <div class="col-md-6 text-end">
                <h5 class="mb-0">Date: <?= date('d M Y', strtotime($invoice['invoiceDate'])) ?></h5>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Invoice Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted">Customer Information:</h6>
                <p class="mb-0">
                    <strong><?= e($invoice['customerName']) ?></strong>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h6 class="text-muted">Issued By:</h6>
                <p class="mb-0">
                    <strong><?= e($invoice['username']) ?></strong>
                </p>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Product Code</th>
                        <th width="35%">Product Name</th>
                        <th width="15%" class="text-center">Quantity</th>
                        <th width="17%" class="text-end">Unit Price</th>
                        <th width="18%" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($invoice['items'])): ?>
                        <?php foreach ($invoice['items'] as $item): ?>
                            <tr>
                                <td><?= e($item['productCode']) ?></td>
                                <td><?= e($item['productName']) ?></td>
                                <td class="text-center"><?= $item['quantitySold'] ?></td>
                                <td class="text-end"><?= formatCurrency($item['price']) ?></td>
                                <td class="text-end"><strong><?= formatCurrency($item['totalPrice']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                        <td class="text-end">
                            <h5 class="mb-0 text-primary">
                                <strong><?= formatCurrency($invoice['totalAmount']) ?></strong>
                            </h5>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    .btn, .navbar, .alert {
        display: none !important;
    }
    .card {
        border: none !important;
    }
    body {
        padding-top: 0 !important;
    }
}
</style>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>