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

class Database {
    // Singleton instance - only one Database object can exist
    private static $instance = null;
    
    // Database connection object
    private $connection = null;
    
    // Database configuration
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';  // XAMPP default password is empty
    private $database = 'smart_inventory_v2';
    
    /**
     * Private constructor - prevents creating multiple instances
     * This is part of the Singleton pattern
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get the single instance of Database (Singleton pattern)
     * If no instance exists, create one. Otherwise return existing instance.
     * 
     * @return Database The database instance
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    /**
     * Connect to MySQL database
     * Uses mysqli extension to establish connection
     */
    private function connect() {
        // Create new mysqli connection
        $this->connection = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );
        
        // Check if connection was successful
        if ($this->connection->connect_error) {
            die("Database connection failed: " . $this->connection->connect_error);
        }
        
        // Set character encoding to UTF-8
        $this->connection->set_charset('utf8mb4');
    }
    
    /**
     * Get the mysqli connection object
     * Other classes use this to run queries
     * 
     * @return mysqli The database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a SQL query
     * 
     * @param string $query The SQL query to execute
     * @return mysqli_result|bool Query result or false on failure
     */
    public function query($query) {
        $result = $this->connection->query($query);
        
        if (!$result) {
            die("Query failed: " . $this->connection->error);
        }
        
        return $result;
    }
    
    /**
     * Get the ID of the last inserted row
     * Useful after INSERT queries
     * 
     * @return int The last insert ID
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Close the database connection
     * Should be called when done with database operations
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}