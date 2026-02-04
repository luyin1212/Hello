<?php
/**
 * Sales Transaction Class
 * Smart Inventory Management System
 * 
 * Handles sales recording and stock updates with automatic
 * inventory adjustments.
 * 
 * Reference:
 * Elmasri, R., & Navathe, S. B. (2015). Fundamentals of Database Systems 
 * (7th ed.). Pearson.
 */

class Sale {
    private $db;
    private $saleID;
    private $invoiceID;
    private $productID;
    private $quantitySold;
    private $saleDate;
    private $totalPrice;
    
    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Record a sale transaction
     * Automatically updates product inventory
     * 
     * @param int $productID Product being sold
     * @param int $quantitySold Quantity sold
     * @param int|null $invoiceID Optional invoice ID
     * @return bool|int Sale ID if successful, false otherwise
     */
    public function recordSale($productID, $quantitySold, $invoiceID = null) {
        // Validate input
        if (empty($productID) || $quantitySold <= 0) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Start transaction for data consistency
        $conn->begin_transaction();
        
        try {
            // Get product details and check stock
            $productObj = new Product();
            $product = $productObj->getProductByID($productID);
            
            if (!$product) {
                throw new Exception("Product not found");
            }
            
            if ($product['quantity'] < $quantitySold) {
                throw new Exception("Insufficient stock");
            }
            
            // Calculate total price
            $totalPrice = $product['price'] * $quantitySold;
            $saleDate = date('Y-m-d');
            
            // Insert sale record
            $sql = "INSERT INTO sale (invoiceID, productID, quantitySold, saleDate, totalPrice) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiisd", $invoiceID, $productID, $quantitySold, $saleDate, $totalPrice);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to record sale");
            }
            
            $saleID = $conn->insert_id;
            $stmt->close();
            
            // Update product quantity (reduce stock)
            if (!$productObj->updateQuantity($productID, -$quantitySold)) {
                throw new Exception("Failed to update inventory");
            }
            
            // Commit transaction
            $conn->commit();
            
            return $saleID;
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Get all sales records
     * 
     * @param int|null $limit Optional limit
     * @return array Array of sales records
     */
    public function getAllSales($limit = null) {
        $sql = "SELECT s.*, p.name as productName, p.productCode 
                FROM sale s 
                LEFT JOIN product p ON s.productID = p.productID 
                ORDER BY s.saleDate DESC, s.saleID DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $result = $this->db->query($sql);
        
        $sales = array();
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        
        return $sales;
    }
    
    /**
     * Get sales by date range
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Array of sales records
     */
    public function getSalesByDateRange($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, p.name as productName, p.productCode 
                FROM sale s 
                LEFT JOIN product p ON s.productID = p.productID 
                WHERE s.saleDate BETWEEN ? AND ? 
                ORDER BY s.saleDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sales = array();
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        
        $stmt->close();
        return $sales;
    }
    
    /**
     * Get sales for a specific product
     * 
     * @param int $productID Product ID
     * @return array Array of sales records
     */
    public function getSalesByProduct($productID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, p.name as productName, p.productCode 
                FROM sale s 
                LEFT JOIN product p ON s.productID = p.productID 
                WHERE s.productID = ? 
                ORDER BY s.saleDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sales = array();
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        
        $stmt->close();
        return $sales;
    }
    
    /**
     * Get total sales amount for a date range
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return float Total sales amount
     */
    public function getTotalSalesAmount($startDate = null, $endDate = null) {
        $conn = $this->db->getConnection();
        
        if ($startDate && $endDate) {
            $sql = "SELECT SUM(totalPrice) as total FROM sale WHERE saleDate BETWEEN ? AND ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
        } else {
            $result = $this->db->query("SELECT SUM(totalPrice) as total FROM sale");
            $row = $result->fetch_assoc();
        }
        
        return $row['total'] ?? 0;
    }
    
    /**
     * Get sales count
     * 
     * @return int Total number of sales
     */
    public function getTotalSalesCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM sale");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    /**
     * Get today's sales
     * 
     * @return array Array of today's sales records
     */
    public function getTodaySales() {
        $today = date('Y-m-d');
        return $this->getSalesByDateRange($today, $today);
    }
    
    /**
     * Get sales statistics
     * 
     * @return array Array containing various statistics
     */
    public function getSalesStatistics() {
        $stats = array();
        
        // Today's sales
        $today = date('Y-m-d');
        $stats['today_count'] = count($this->getTodaySales());
        $stats['today_amount'] = $this->getTotalSalesAmount($today, $today);
        
        // This month's sales
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $stats['month_count'] = count($this->getSalesByDateRange($monthStart, $monthEnd));
        $stats['month_amount'] = $this->getTotalSalesAmount($monthStart, $monthEnd);
        
        // All time
        $stats['total_count'] = $this->getTotalSalesCount();
        $stats['total_amount'] = $this->getTotalSalesAmount();
        
        return $stats;
    }
    
    /**
     * Delete a sale record
     * Note: This should restore inventory, but be careful with data integrity
     * 
     * @param int $saleID Sale ID to delete
     * @return bool True if deleted successfully
     */
    public function deleteSale($saleID) {
        // Validate sale ID
        if (empty($saleID)) {
            return false;
        }
        
        $conn = $this->db->getConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get sale details before deletion
            $sql = "SELECT * FROM sale WHERE saleID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $saleID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                throw new Exception("Sale not found");
            }
            
            $sale = $result->fetch_assoc();
            $stmt->close();
            
            // Restore inventory
            $productObj = new Product();
            if (!$productObj->updateQuantity($sale['productID'], $sale['quantitySold'])) {
                throw new Exception("Failed to restore inventory");
            }
            
            // Delete sale record
            $sql = "DELETE FROM sale WHERE saleID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $saleID);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete sale");
            }
            
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            return false;
        }
    }
}