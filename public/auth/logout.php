<?php
/**
 * Logout Page
 * Logs out the current user and redirects to login page
 */

require_once __DIR__ . '/../../config/config.php';

// Create User object
$user = new User();

// Call logout method
$user->logout();

// Redirect to login page with success message
redirect(getBaseURL() . '/public/auth/login.php?logout=success');