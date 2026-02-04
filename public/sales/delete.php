<?php
/**
 * Delete Sale
 * Handles sale deletion and inventory restoration
 */

require_once __DIR__ . '/../../config/config.php';

// Must be logged in
User::requireAuth();

// Get sale ID
$saleID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$saleID) {
    redirect(getBaseURL() . '/public/sales/index.php?error=Invalid sale ID');
}

// Create Sale object
$sale = new Sale();

// Try to delete (will restore inventory automatically)
if ($sale->deleteSale($saleID)) {
    redirect(getBaseURL() . '/public/sales/index.php?success=deleted');
} else {
    redirect(getBaseURL() . '/public/sales/index.php?error=Failed to delete sale record');
}