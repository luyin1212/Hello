<?php
/**
 * Application Configuration File
 * Smart Inventory Management System
 * 
 * Defines application constants, initializes session management,
 * and implements PSR-4 compliant class autoloading.
 * 
 * Autoloading Reference:
 * PHP-FIG. (2013). PSR-4: Autoloader.
 * Retrieved from https://www.php-fig.org/psr/psr-4/
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define path constants for easy file access
define('BASE_PATH', dirname(__DIR__));
define('CLASSES_PATH', BASE_PATH . '/classes');
define('PUBLIC_PATH', BASE_PATH . '/public');

/**
 * Automatic class loader
 * When you use "new ClassName()", PHP will automatically load the class file
 * This saves us from writing "require_once" everywhere
 */
spl_autoload_register(function ($className) {
    $classFile = CLASSES_PATH . '/' . $className . '.php';
    
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Error reporting - show all errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Europe/London');

// Application information
define('APP_NAME', 'Smart Inventory Management System');
define('APP_VERSION', '2.0.0');

/**
 * Get the base URL of the application
 * @return string The base URL
 */
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/SmartInventory_v2';
}

/**
 * Redirect to another page
 * @param string $url The URL to redirect to
 */
function redirect($url) {
    echo "<script>window.location.href='" . $url . "';</script>";
    exit();
}

/**
 * Safely output HTML - prevents XSS attacks
 * @param string $string The string to output
 * @return string The safe HTML string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format a number as currency (British Pounds)
 * @param float $amount The amount to format
 * @return string Formatted currency string
 */
function formatCurrency($amount) {
    return 'RM' . number_format($amount, 2);
}

/**
 * Format a date in readable format
 * @param string $date The date to format
 * @return string Formatted date string
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}