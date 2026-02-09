<?php
/**
 * 404 Error Page
 * Page not found
 */

require_once __DIR__ . '/../config/config.php';

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 offset-md-3 text-center">
        <div class="card mt-5">
            <div class="card-body py-5">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                <h1 class="mt-4">404</h1>
                <h4>Page Not Found</h4>
                <p class="text-muted mt-3">
                    The page you are looking for doesn't exist or has been moved.
                </p>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="<?= getBaseURL() ?>/public/index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Go to Dashboard
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>