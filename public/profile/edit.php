<?php
/**
 * Edit Profile Page
 * Allow users to edit their profile information
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';

// Get current user
$userObj = new User();
$currentUser = $userObj->getUserByID($_SESSION['userID']);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    
    // Validate
    if (empty($username)) {
        $error = 'Username is required.';
    } else {
        // Check if username is taken by another user
        $conn = Database::getInstance()->getConnection();
        $checkSql = "SELECT userID FROM user WHERE username = ? AND userID != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $username, $_SESSION['userID']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username is already taken.';
        } else {
            // Update user info
            $sql = "UPDATE user SET username = ?, WHERE userID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $username, $_SESSION['userID']);
            
            if ($stmt->execute()) {
                $_SESSION['username'] = $username; // Update session
                $success = 'Profile updated successfully!';
                $currentUser['username'] = $username;
                // $currentUser['email'] = $email;
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
            
            $stmt->close();
        }
        
        $checkStmt->close();
    }
}
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-pencil"></i> Edit Profile
        </h2>
        <p class="text-muted">Update your profile information</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-badge"></i> Profile Information
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
                        <label for="username" class="form-label">
                            Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= e($currentUser['username']) ?>"
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?= e($currentUser['role']) ?>"
                               disabled>
                        <small class="text-muted">Role cannot be changed</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Changes
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