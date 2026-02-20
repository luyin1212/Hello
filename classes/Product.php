<?php
/**
 * Product Management Class
 * Smart Inventory Management System
 * 
 * Implements CRUD operations for product inventory management.
 * Follows object-oriented design principles for data encapsulation
 * and business logic separation.
 * 
 * Reference:
 * Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.
 */

class Product {
    private $db;
    private $productID;
    private $productCode;
    private $name;
    private $category;
    private $price;
    private $quantity;
    private $reorderLevel;
    
    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Add a new product to inventory
     * Matches addProduct() from Class Diagram
     * 
     * @param string $productCode Unique product code
     * @param string $name Product name
     * @param string $category Product category
     * @param float $price Product price
     * @param int $quantity Initial quantity
     * @param int $reorderLevel Reorder threshold
     * @return bool True if product added successfully
     */
    public function addProduct($productCode, $name, $category, $price, $quantity, $reorderLevel = 10) {
        // Validate required fields
        if (empty($productCode) || empty($name)) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Check if product code already exists
        $checkSql = "SELECT productID FROM product WHERE productCode = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $productCode);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $checkStmt->close();
            return false; // Product code already exists
        }
        $checkStmt->close();
        
        // Insert new product
        $sql = "INSERT INTO product (productCode, name, category, price, quantity, reorderLevel) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdii", $productCode, $name, $category, $price, $quantity, $reorderLevel);
        
        if ($stmt->execute()) {
            $this->productID = $conn->insert_id;
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Update an existing product
     * Matches updateProduct() from Class Diagram
     * 
     * @param int $productID Product ID to update
     * @param string $productCode Product code
     * @param string $name Product name
     * @param string $category Product category
     * @param float $price Product price
     * @param int $quantity Current quantity
     * @param int $reorderLevel Reorder threshold
     * @return bool True if updated successfully
     */
    public function updateProduct($productID, $productCode, $name, $category, $price, $quantity, $reorderLevel) {
        // Validate required fields
        if (empty($productID) || empty($productCode) || empty($name)) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Check if product code exists for another product
        $checkSql = "SELECT productID FROM product WHERE productCode = ? AND productID != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $productCode, $productID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $checkStmt->close();
            return false; // Product code already used by another product
        }
        $checkStmt->close();
        
        // Update product
        $sql = "UPDATE product 
                SET productCode = ?, name = ?, category = ?, price = ?, quantity = ?, reorderLevel = ? 
                WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdiii", $productCode, $name, $category, $price, $quantity, $reorderLevel, $productID);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Delete a product
     * Matches deleteProduct() from Class Diagram
     * 
     * @param int $productID Product ID to delete
     * @return bool True if deleted successfully
     */
    public function deleteProduct($productID) {
        // Validate product ID
        if (empty($productID)) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Check if product has sales records
        $checkSql = "SELECT COUNT(*) as count FROM sale WHERE productID = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $productID);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        $checkStmt->close();
        
        if ($row['count'] > 0) {
            // Product has sales records, cannot delete
            return false;
        }
        
        // Delete product
        $sql = "DELETE FROM product WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productID);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Get all products
     * 
     * @return array Array of all products
     */
    public function getAllProducts() {
        $sql = "SELECT * FROM product ORDER BY name ASC";
        $result = $this->db->query($sql);
        
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Get a single product by ID
     * 
     * @param int $productID Product ID
     * @return array|null Product data or null if not found
     */
    public function getProductByID($productID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT * FROM product WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $stmt->close();
            return $product;
        }
        
        $stmt->close();
        return null;
    }
    
    /**
     * Search products by keyword
     * Matches searchProduct() from Class Diagram
     * 
     * @param string $keyword Search keyword
     * @return array Array of matching products
     */
    public function searchProduct($keyword) {
        if (empty($keyword)) {
            return $this->getAllProducts();
        }
        
        $conn = $this->db->getConnection();
        
        $searchTerm = "%{$keyword}%";
        $sql = "SELECT * FROM product 
                WHERE name LIKE ? 
                   OR productCode LIKE ? 
                   OR category LIKE ? 
                ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        return $products;
    }
    
    /**
     * Get products that are low in stock
     * 
     * ALGORITHM: Filtering with Comparison
     * 
     * Purpose: Identify products needing restock
     * Condition: quantity ≤ reorderLevel
     * 
     * Algorithm Type: Conditional selection with sorting
     * Time Complexity: O(n log n) due to ORDER BY
     * 
     * This powers the alert system by identifying low-stock items.
     * 
     * @return array Array of low stock products
     */
    public function getLowStockProducts() {
        $sql = "SELECT * FROM product WHERE quantity <= reorderLevel ORDER BY quantity ASC";
        $result = $this->db->query($sql);
        
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Update product quantity
     * Used when sales are made or invoices are deleted
     * 
     * ALGORITHM: Dynamic Inventory Update
     * 
     * Purpose: Modify product stock levels in real-time
     * Formula: newQuantity = currentQuantity + quantityChange
     * 
     * Algorithm Type: Addition/Subtraction with SQL atomic operation
     * Time Complexity: O(1) - constant time operation
     * 
     * Reference: Elmasri & Navathe (2015)
     * Fundamentals of Database Systems
     * Chapter 21: Concurrency Control - Atomic Operations
     * 
     * @param int $productID Product ID
     * @param int $quantityChange Change in quantity (negative for sales)
     * @return bool True if updated successfully
     */
    public function updateQuantity($productID, $quantityChange) {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE product SET quantity = quantity + ? WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantityChange, $productID);
        
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Get total number of products
     * 
     * ALGORITHM: Aggregation - COUNT
     * 
     * Purpose: Count total products in inventory
     * Time Complexity: O(n) where n = number of products
     * 
     * @return int Total product count
     */
    public function getTotalProducts() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM product");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    /**
     * Get total inventory value
     * 
     * ALGORITHM: Aggregation with Multiplication
     * 
     * Purpose: Calculate total value of all inventory
     * Formula: totalValue = Σ(price × quantity) for all products
     * 
     * Algorithm Type: SUM with inline multiplication
     * Time Complexity: O(n) where n = number of products
     * 
     * @return float Total value of all products
     */
    public function getTotalInventoryValue() {
        $result = $this->db->query("SELECT SUM(price * quantity) as total FROM product");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}