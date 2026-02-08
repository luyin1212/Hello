<?php
/**
 * Database Connection Class
 * Smart Inventory Management System
 * 
 * Implements Singleton design pattern to ensure only one database 
 * connection exists throughout the application lifecycle.
 * 
 * Design Pattern Reference:
 * Gamma, E., Helm, R., Johnson, R., & Vlissides, J. (1994). 
 * Design Patterns: Elements of Reusable Object-Oriented Software. 
 * Addison-Wesley.
 */

class User {
    private $db;
    private $userID;
    private $username;
    private $role;
    
    /**
     * Constructor
     * Initializes database and starts session
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * User login function
     * Matches the login() method from the Class Diagram
     * 
     * @param string $username User's username
     * @param string $password User's password
     * @return bool True if login successful, false otherwise
     */
    public function login($username, $password) {
        // Validate input
        if (empty($username) || empty($password)) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Prepare SQL query to prevent SQL injection
        $sql = "SELECT * FROM user WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using password_verify
            // This compares the entered password with the hashed password in database
            if (password_verify($password, $user['password'])) {
                // Login successful - store user info in session
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['loggedin'] = true;
                
                // Also store in object properties
                $this->userID = $user['userID'];
                $this->username = $user['username'];
                $this->role = $user['role'];
                
                $stmt->close();
                return true;
            }
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * User logout function
     * Matches the logout() method from the Class Diagram
     * Destroys the session and clears user data
     */
    public function logout() {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        // Clear object properties
        $this->userID = null;
        $this->username = null;
        $this->role = null;
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public function isLoggedIn() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            return true;
        }
        return false;
    }
    
    /**
     * Get current user ID from session
     * 
     * @return int|null User ID or null if not logged in
     */
    public function getUserID() {
        if (isset($_SESSION['userID'])) {
            return $_SESSION['userID'];
        }
        return null;
    }
    
    /**
     * Get current username from session
     * 
     * @return string|null Username or null if not logged in
     */
    public function getUsername() {
        if (isset($_SESSION['username'])) {
            return $_SESSION['username'];
        }
        return null;
    }
    
    /**
     * Get current user role from session
     * 
     * @return string|null User role or null if not logged in
     */
    public function getRole() {
        if (isset($_SESSION['role'])) {
            return $_SESSION['role'];
        }
        return null;
    }
    
    /**
     * Create a new user account
     * 
     * @param string $username Username for new account
     * @param string $password Password for new account
     * @param string $role User role (default: 'user')
     * @return bool True if user created successfully
     */
    public function createUser($username, $password, $role = 'user') {
        // Validate input
        if (empty($username) || empty($password)) {
            return false;
        }
        
        // Hash the password for security
        // Never store plain text passwords in database!
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Insert new user into database
        $sql = "INSERT INTO user (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Require authentication for a page
     * If user is not logged in, redirect to login page
     * 
     * @param string $loginPage URL of login page
     */
    public static function requireAuth($loginPage = '/SmartInventory_v2/public/auth/login.php') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
            header("Location: $loginPage");
            exit();
        }
    }

        /**
     * Get user by ID
     * 
     * @param int $userID User ID
     * @return array|null User data or null if not found
     */
    public function getUserByID($userID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT userID, username, role FROM user WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stmt->close();
            return $user;
        }
        
        $stmt->close();
        return null;
    }
    
}