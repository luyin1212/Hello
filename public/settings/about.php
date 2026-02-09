<?php
/**
 * About Page
 * Information about the system
 */

require_once __DIR__ . '/../../config/config.php';

// Include header
include_once __DIR__ . '/../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="text-primary">
            <i class="bi bi-info-circle"></i> About
        </h2>
        <p class="text-muted">About Smart Inventory Management System</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- System Information -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="bi bi-box-seam text-primary" style="font-size: 5rem;"></i>
                <h2 class="mt-3"><?= APP_NAME ?></h2>
                <p class="lead">Version <?= APP_VERSION ?></p>
                <p class="text-muted">
                    A comprehensive inventory management system for small to medium businesses
                </p>
            </div>
        </div>
        
        <!-- Features -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-star"></i> Key Features
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                User Authentication
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                Product Management
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                Invoice Generation
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                Low Stock Alerts
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                Sales Reports
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success"></i> 
                                Dashboard Analytics
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Technology Stack -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-code-square"></i> Technology Stack
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <i class="bi bi-filetype-php" style="font-size: 3rem; color: #777BB4;"></i>
                        <h6 class="mt-2">PHP <?= phpversion() ?></h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <i class="bi bi-database" style="font-size: 3rem; color: #00758F;"></i>
                        <h6 class="mt-2">MySQL</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <i class="bi bi-bootstrap" style="font-size: 3rem; color: #7952B3;"></i>
                        <h6 class="mt-2">Bootstrap 5</h6>
                    </div>
                    <div class="col-md-3 mb-3">
                        <i class="bi bi-filetype-html" style="font-size: 3rem; color: #E34F26;"></i>
                        <h6 class="mt-2">HTML5/CSS3</h6>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- References -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-book"></i> Academic References
            </div>
            <div class="card-body">
                <p><strong>Design Patterns:</strong></p>
                <p class="small">
                    Gamma, E., Helm, R., Johnson, R., & Vlissides, J. (1994). 
                    <em>Design Patterns: Elements of Reusable Object-Oriented Software.</em> 
                    Addison-Wesley.
                </p>
                
                <p class="mt-3"><strong>Software Engineering:</strong></p>
                <p class="small">
                    Sommerville, I. (2016). 
                    <em>Software Engineering (10th ed.).</em> 
                    Pearson.
                </p>
                
                <p class="mt-3"><strong>Database Systems:</strong></p>
                <p class="small">
                    Elmasri, R., & Navathe, S. B. (2015). 
                    <em>Fundamentals of Database Systems (7th ed.).</em> 
                    Pearson.
                </p>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="card">
            <div class="card-body text-center">
                <p class="mb-0">
                    <small class="text-muted">
                        © 2026 Smart Inventory Management System<br>
                        Developed as part of academic coursework
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>