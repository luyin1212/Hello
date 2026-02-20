<?php
/**
 * Sales Transaction Class
 * Smart Inventory Management System
 * 
 * Handles individual sale items within invoices.
 * Each sale represents one product line in an invoice.
 * Implements the sale recording process as defined in the system's
 * sequence diagram where sales are created as part of invoice generation.
 * 
 * Reference:
 * Elmasri, R., & Navathe, S. B. (2015). Fundamentals of Database Systems 
 * (7th ed.). Pearson.
 * - Chapter 7: Relational Database Design by ER- and EER-to-Relational Mapping
 * - Used for understanding foreign key relationships between Sale and Invoice tables
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
     * Initializes database connection using Singleton pattern
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Record a sale item
     * Part of invoice creation process
     * Matches RecordSale() from Class Diagram
     * 
     * This method is called by Invoice::generateInvoice() to record
     * individual product sales within an invoice.
     * 
     * @param int $invoiceID Invoice this sale belongs to
     * @param int $productID Product being sold
     * @param int $quantitySold Quantity sold
     * @return bool|int Sale ID if successful, false otherwise
     */
    public function recordSale($invoiceID, $productID, $quantitySold) {
        // Validate input
        if (empty($productID) || $quantitySold <= 0) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Get product details
        $productObj = new Product();
        $product = $productObj->getProductByID($productID);
        
        if (!$product) {
            return false;
        }
        
        // Check stock availability
        if ($product['quantity'] < $quantitySold) {
            return false;
        }
        
        // Calculate total price for this sale item
        $totalPrice = $product['price'] * $quantitySold;
        $saleDate = date('Y-m-d');
        
        // Insert sale record
        $sql = "INSERT INTO sale (invoiceID, productID, quantitySold, saleDate, totalPrice) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisd", $invoiceID, $productID, $quantitySold, $saleDate, $totalPrice);
        
        if ($stmt->execute()) {
            $saleID = $conn->insert_id;
            $stmt->close();
            
            /**
             * ALGORITHM: Inventory Deduction
             * 
             * Purpose: Automatically reduce product stock when sale is recorded
             * Formula: newQuantity = currentQuantity - quantitySold
             * 
             * Algorithm Type: Subtraction with database update
             * Time Complexity: O(1) - constant time operation
             * 
             * Example: If laptop stock is 15, selling 2 units → new stock = 13
             * 
             * This ensures real-time inventory accuracy and prevents overselling.
             * 
             * Reference: Sommerville (2016), Software Engineering
             * Chapter 15: Dependability and Security (Data Consistency)
             */
            // Update product quantity (reduce stock)
            $productObj->updateQuantity($productID, -$quantitySold);
            
            return $saleID;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Calculate total price for a sale item
     * Matches CalculateTotal() from Class Diagram
     * 
     * ALGORITHM: Multiplication
     * 
     * Purpose: Calculate line item total
     * Formula: totalPrice = unitPrice × quantity
     * Time Complexity: O(1) - constant time
     * 
     * Example: Price RM 100 × Quantity 3 = RM 300
     * 
     * Used during invoice generation to calculate subtotals
     * before recording the sale.
     * 
     * @param int $productID Product ID
     * @param int $quantity Quantity
     * @return float Total price
     */
    public function calculateTotal($productID, $quantity) {
        $productObj = new Product();
        $product = $productObj->getProductByID($productID);
        
        if ($product) {
            return $product['price'] * $quantity;
        }
        
        return 0;
    }
    
    /**
     * Get sales by invoice ID
     * 
     * Retrieves all sale items associated with a specific invoice.
     * Used when displaying invoice details or generating PDF reports.
     * 
     * @param int $invoiceID Invoice ID
     * @return array Array of sale items with product information
     */
    public function getSalesByInvoice($invoiceID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT s.*, p.name as productName, p.productCode, p.price as unitPrice 
                FROM sale s 
                LEFT JOIN product p ON s.productID = p.productID 
                WHERE s.invoiceID = ? 
                ORDER BY s.saleID ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $invoiceID);
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
     * Get all sales records
     * 
     * Retrieves complete sales history with product information.
     * Used for sales reports and analytics.
     * 
     * @return array Array of all sales with product details
     */
    public function getAllSales() {
        $sql = "SELECT s.*, p.name as productName, p.productCode 
                FROM sale s 
                LEFT JOIN product p ON s.productID = p.productID 
                ORDER BY s.saleDate DESC, s.saleID DESC";
        
        $result = $this->db->query($sql);
        
        $sales = array();
        while ($row = $result->fetch_assoc()) {
            $sales[] = $row;
        }
        
        return $sales;
    }
    
    /**
     * Delete sales by invoice ID
     * Used when deleting an invoice
     * 
     * Implements cascading delete with inventory restoration.
     * Ensures data integrity by restoring product quantities
     * when an invoice is deleted.
     * 
     * @param int $invoiceID Invoice ID
     * @return bool True if successful
     */
    public function deleteSalesByInvoice($invoiceID) {
        $conn = $this->db->getConnection();
        
        /**
         * ALGORITHM: Inventory Restoration
         * 
         * Purpose: Restore product stock when invoice is deleted
         * Formula: newQuantity = currentQuantity + quantitySold
         * 
         * Algorithm Type: Addition with loop iteration
         * Time Complexity: O(n) where n = number of items in invoice
         * 
         * Process:
         * 1. Get all sale items from the invoice
         * 2. For each sale item:
         *    - Add sold quantity back to product stock
         * 3. Delete sale records
         * 
         * Example: If 2 laptops were sold, deleting invoice adds 2 back to stock
         * 
         * This maintains data integrity - ensures inventory is always accurate
         * even when transactions are reversed.
         * 
         * Reference: Elmasri & Navathe (2015)
         * Database Systems - Transaction Management and Data Consistency
         */
        // First, restore inventory for all sales in this invoice
        $sales = $this->getSalesByInvoice($invoiceID);
        $productObj = new Product();
        
        foreach ($sales as $sale) {
            // Restore quantity (add back to stock)
            $productObj->updateQuantity($sale['productID'], $sale['quantitySold']);
        }
        
        // Delete all sales for this invoice
        $sql = "DELETE FROM sale WHERE invoiceID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $invoiceID);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Get total sales count
     * 
     * ALGORITHM: Aggregation - COUNT
     * 
     * Purpose: Count total number of sale transactions
     * Time Complexity: O(n) where n = number of sale records
     * 
     * Returns the total number of sale items in the system.
     * Used for dashboard statistics and reporting.
     * 
     * @return int Total number of sale items
     */
    public function getTotalSalesCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM sale");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}