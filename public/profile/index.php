<?php
/**
 * User Profile Page
 * Display and manage user information
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Get current user info
$userObj = new User();
$currentUser = $userObj->getUserByID($_SESSION['userID']);

if (!$currentUser) {
    redirect(getBaseURL() . '/public/auth/logout.php');
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-person-circle"></i> My Profile
        </h2>
        <p class="text-muted">Manage your account information</p>
    </div>
</div>

<div class="row">
    <!-- Profile Information Card -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-badge"></i> Profile Information
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle text-primary" style="font-size: 5rem;"></i>
                    <h4 class="mt-2"><?= e($currentUser['username']) ?></h4>
                    <p class="text-muted"><?= e($currentUser['role']) ?></p>
                </div>
                
                <table class="table">
                    <tr>
                        <th width="40%">User ID:</th>
                        <td><?= e($currentUser['userID']) ?></td>
                    </tr>
                    <tr>
                        <th>Username:</th>
                        <td><?= e($currentUser['username']) ?></td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td>
                            <span class="badge bg-primary"><?= e($currentUser['role']) ?></span>
                        </td>
                    </tr>
                </table>
                
                <div class="d-grid gap-2">
                    <a href="edit.php" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                    <a href="change-password.php" class="btn btn-outline-secondary">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Activity Summary Card -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-graph-up"></i> My Activity
            </div>
            <div class="card-body">
                <?php
                // Get user's activity stats
                $invoiceObj = new Invoice();
                $userInvoices = 0;
                $allInvoices = $invoiceObj->getAllInvoices();
                foreach ($allInvoices as $inv) {
                    if ($inv['userID'] == $_SESSION['userID']) {
                        $userInvoices++;
                    }
                }
                ?>
                
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-receipt text-success" style="font-size: 2rem;"></i>
                            <h3 class="mt-2"><?= $userInvoices ?></h3>
                            <p class="text-muted mb-0">Invoices Created</p>
                        </div>
                    </div>
                    
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-clock-history text-info" style="font-size: 2rem;"></i>
                            <h3 class="mt-2">0</h3>
                            <p class="text-muted mb-0">Days Active</p>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6>Recent Activity</h6>
                <div class="list-group list-group-flush">
                    <?php
                    $recentInvoices = array_slice($allInvoices, 0, 3);
                    if (count($recentInvoices) > 0):
                        foreach ($recentInvoices as $inv):
                            if ($inv['userID'] == $_SESSION['userID']):
                    ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>
                                    <i class="bi bi-receipt"></i> 
                                    Invoice #<?= str_pad($inv['invoiceID'], 4, '0', STR_PAD_LEFT) ?>
                                </span>
                                <small class="text-muted">
                                    <?= date('d M Y', strtotime($inv['invoiceDate'])) ?>
                                </small>
                            </div>
                        </div>
                    <?php
                            endif;
                        endforeach;
                    else:
                    ?>
                        <div class="alert alert-info mb-0">
                            <small>No recent activity</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>