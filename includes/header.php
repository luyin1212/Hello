<?php
/**
 * Common Header
 * Includes navigation and user menu
 */

// Prevent direct access
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    exit("Direct access not allowed");
}

// Check if user is logged in (except for login page)
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage != 'login.php') {
    User::requireAuth();
}

// Get current user info
$user = new User();
$currentUsername = $user->getUsername() ?? 'Guest';
$currentRole = $user->getRole() ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f8f9fa; 
            padding-top: 70px;
        }
        .navbar-brand { 
            font-weight: bold; 
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= getBaseURL() ?>/public/index.php">
            <i class="bi bi-box-seam"></i> Smart Inventory
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/products/index.php">
                        <i class="bi bi-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/Sales/index.php">
                        <i class="bi bi-cart-check"></i> Sales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/invoices/index.php">
                        <i class="bi bi-receipt"></i> Invoices
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/alerts/index.php">
                        <i class="bi bi-exclamation-triangle"></i> Alerts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= getBaseURL() ?>/public/reports/index.php">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </li>
            </ul>
            
            <!-- User Menu -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?= e($currentUsername) ?>
                        <?php if ($currentRole == 'admin'): ?>
                            <span class="badge bg-warning text-dark">Admin</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="<?= getBaseURL() ?>/public/profile/index.php">
                                <i class="bi bi-person-badge"></i> My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= getBaseURL() ?>/public/profile/change-password.php">
                                <i class="bi bi-key"></i> Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= getBaseURL() ?>/public/auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">