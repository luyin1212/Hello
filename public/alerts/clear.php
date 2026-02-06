<?php
/**
 * Clear Alert
 * Remove a specific alert
 */

require_once __DIR__ . '/../../config/config.php';

// Must be logged in
User::requireAuth();

// Get alert ID
$alertID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$alertID) {
    redirect(getBaseURL() . '/public/alerts/index.php?error=Invalid alert ID');
}

// Create Alert object
$alertObj = new Alert();

// Try to delete
if ($alertObj->deleteAlert($alertID)) {
    redirect(getBaseURL() . '/public/alerts/index.php?success=cleared');
} else {
    redirect(getBaseURL() . '/public/alerts/index.php?error=Failed to clear alert');
}