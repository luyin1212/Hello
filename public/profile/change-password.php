<?php
/**
 * Change Password Page
 * Allow users to change their password
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validate
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } else {
        // Verify current password
        $userObj = new User();
        $user = $userObj->getUserByID($_SESSION['userID']);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update password
            $conn = Database::getInstance()->getConnection();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $sql = "UPDATE user SET password = ? WHERE userID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $_SESSION['userID']);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
                $currentPassword = $newPassword = $confirmPassword = '';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
            
            $stmt->close();
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-key"></i> Change Password
        </h2>
        <p class="text-muted">Update your account password</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-shield-lock"></i> Change Password
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?= e($success) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="currentPassword" 
                               name="currentPassword" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="newPassword" 
                               name="newPassword" 
                               required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="confirmPassword" 
                               name="confirmPassword" 
                               required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Change Password
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>