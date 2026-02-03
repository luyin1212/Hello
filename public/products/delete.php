<?php
/**
 * Delete Product
 * Handles product deletion
 */

require_once __DIR__ . '/../../config/config.php';

// Must be logged in
User::requireAuth();

// Get product ID
$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productID) {
    redirect(getBaseURL() . '/public/products/index.php?error=Invalid product ID');
}

// Create Product object
$product = new Product();

// Try to delete
if ($product->deleteProduct($productID)) {
    redirect(getBaseURL() . '/public/products/index.php?success=deleted');
} else {
    redirect(getBaseURL() . '/public/products/index.php?error=Cannot delete product. It may have sales records.');
}