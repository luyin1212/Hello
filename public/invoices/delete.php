<?php
/**
 * Delete Invoice
 * Handles invoice deletion and inventory restoration
 */

require_once __DIR__ . '/../../config/config.php';

// Must be logged in
User::requireAuth();

// Get invoice ID
$invoiceID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$invoiceID) {
    redirect(getBaseURL() . '/public/invoices/index.php?error=Invalid invoice ID');
}

// Create Invoice object
$invoiceObj = new Invoice();

// Try to delete (will restore inventory automatically)
if ($invoiceObj->deleteInvoice($invoiceID)) {
    redirect(getBaseURL() . '/public/invoices/index.php?success=deleted');
} else {
    redirect(getBaseURL() . '/public/invoices/index.php?error=Failed to delete invoice');
}