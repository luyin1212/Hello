<?php
/**
 * Common Footer
 */

// Prevent direct access
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    exit("Direct access not allowed");
}
?>

</div> <!-- Close container from header -->

<footer class="mt-5 py-4 bg-light border-top">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    <i class="bi bi-box-seam"></i> 
                    <strong><?= APP_NAME ?></strong> v<?= APP_VERSION ?>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y') ?> All rights reserved
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>