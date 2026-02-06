<?php
/**
 * Alert Management Class
 * Smart Inventory Management System
 * 
 * Manages inventory alerts for low stock products.
 * Automatically monitors product quantities and generates
 * alerts when stock falls below reorder levels.
 * 
 * Reference:
 * Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.
 * - Chapter 19: Systems Engineering
 * - Used for implementing automated monitoring and notification systems
 */

class Alert {
    private $db;
    private $alertID;
    private $productID;
    private $name;
    private $message;
    private $alertDate;
    
    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check stock levels and generate alerts
     * Matches CheckStockLevel() from Class Diagram
     * 
     * Scans all products and creates alerts for those
     * with quantities at or below their reorder levels.
     * 
     * @return int Number of alerts generated
     */
    public function checkStockLevel() {
        $conn = $this->db->getConnection();
        $alertCount = 0;
        
        // Get all low stock products
        $sql = "SELECT * FROM product WHERE quantity <= reorderLevel";
        $result = $this->db->query($sql);
        
        while ($product = $result->fetch_assoc()) {
            // Check if alert already exists for this product
            $checkSql = "SELECT alertID FROM alert WHERE productID = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $product['productID']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows == 0) {
                // Create new alert
                $message = "Low stock alert: {$product['name']} has only {$product['quantity']} units remaining (Reorder level: {$product['reorderLevel']})";
                
                if ($this->createAlert($product['productID'], $product['name'], $message)) {
                    $alertCount++;
                }
            }
            
            $checkStmt->close();
        }
        
        return $alertCount;
    }
    
    /**
     * Create a new alert
     * 
     * @param int $productID Product ID
     * @param string $name Product name
     * @param string $message Alert message
     * @return bool True if created successfully
     */
    private function createAlert($productID, $name, $message) {
        $conn = $this->db->getConnection();
        
        $alertDate = date('Y-m-d');
        
        $sql = "INSERT INTO alert (productID, name, message, alertDate) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $productID, $name, $message, $alertDate);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Get all active alerts
     * 
     * @return array Array of alerts
     */
    public function getAllAlerts() {
        $sql = "SELECT a.*, p.quantity, p.reorderLevel, p.productCode 
                FROM alert a 
                LEFT JOIN product p ON a.productID = p.productID 
                ORDER BY a.alertDate DESC, a.alertID DESC";
        
        $result = $this->db->query($sql);
        
        $alerts = array();
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        
        return $alerts;
    }
    
    /**
     * Get alert by ID
     * 
     * @param int $alertID Alert ID
     * @return array|null Alert data or null
     */
    public function getAlertByID($alertID) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT a.*, p.quantity, p.reorderLevel, p.productCode, p.price 
                FROM alert a 
                LEFT JOIN product p ON a.productID = p.productID 
                WHERE a.alertID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alertID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $alert = $result->fetch_assoc();
            $stmt->close();
            return $alert;
        }
        
        $stmt->close();
        return null;
    }
    
    /**
     * Delete an alert
     * Usually called after restocking the product
     * 
     * @param int $alertID Alert ID
     * @return bool True if deleted successfully
     */
    public function deleteAlert($alertID) {
        $conn = $this->db->getConnection();
        
        $sql = "DELETE FROM alert WHERE alertID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $alertID);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Clear alerts for a specific product
     * Called after product is restocked above reorder level
     * 
     * @param int $productID Product ID
     * @return bool True if cleared successfully
     */
    public function clearProductAlerts($productID) {
        $conn = $this->db->getConnection();
        
        $sql = "DELETE FROM alert WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productID);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Get total number of active alerts
     * 
     * @return int Total alerts count
     */
    public function getTotalAlerts() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM alert");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    /**
     * Notify user about alerts
     * Matches NotifyUser() from Class Diagram
     * 
     * In a real system, this would send email/SMS notifications.
     * For now, it returns alert messages for display.
     * 
     * @return array Array of alert messages
     */
    public function notifyUser() {
        $alerts = $this->getAllAlerts();
        $notifications = array();
        
        foreach ($alerts as $alert) {
            $notifications[] = array(
                'alertID' => $alert['alertID'],
                'productName' => $alert['name'],
                'message' => $alert['message'],
                'date' => $alert['alertDate']
            );
        }
        
        return $notifications;
    }
}