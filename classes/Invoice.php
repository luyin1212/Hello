<?php
/**
 * Invoice Management Class
 * Smart Inventory Management System
 * 
 * Manages invoice creation, viewing, and PDF generation.
 * Each invoice can contain multiple sale items.
 * 
 * Reference:
 * Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.
 */

class Invoice {
    private $db;
    private $invoiceID;
    private $userID;
    private $invoiceDate;
    private $totalAmount;
    private $customerName;
    
    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate a new invoice with multiple sale items
     * Matches GenerateInvoice() from Class Diagram
     * 
     * @param int $userID User creating the invoice
     * @param string $customerName Customer name
     * @param array $items Array of items: [{productID, quantity}, ...]
     * @return bool|int Invoice ID if successful, false otherwise
     */
    public function generateInvoice($userID, $customerName, $items) {
        // Validate input
        if (empty($customerName) || empty($items)) {
            return false;
        }
        
        // Get database connection
        $conn = $this->db->getConnection();
        
        // Start transaction for data consistency
        $conn->begin_transaction();
        
        try {
            // Create invoice record first
            $invoiceDate = date('Y-m-d');
            $totalAmount = 0;
            
            /**
             * ALGORITHM: Accumulation (Sum)
             * 
             * Purpose: Calculate total invoice amount
             * Process: Sum up all individual item totals
             * Formula: totalAmount = Σ(price × quantity) for all items
             * Time Complexity: O(n) where n = number of items in invoice
             */
            // Calculate total amount
            $saleObj = new Sale();
            foreach ($items as $item) {
                $itemTotal = $saleObj->calculateTotal($item['productID'], $item['quantity']);
                $totalAmount += $itemTotal;
            }
            
            // Insert invoice
            $sql = "INSERT INTO invoice (userID, invoiceDate, totalAmount, customerName) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isds", $userID, $invoiceDate, $totalAmount, $customerName);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create invoice");
            }
            
            $invoiceID = $conn->insert_id;
            $stmt->close();
            
            // Record each sale item
            foreach ($items as $item) {
                if (!$saleObj->recordSale($invoiceID, $item['productID'], $item['quantity'])) {
                    throw new Exception("Failed to record sale item");
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            return $invoiceID;
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Get all invoices
     * 
     * @return array Array of invoices
     */
    public function getAllInvoices() {
        $sql = "SELECT i.*, u.username 
                FROM invoice i 
                LEFT JOIN user u ON i.userID = u.userID 
                ORDER BY i.invoiceDate DESC, i.invoiceID DESC";
        
        $result = $this->db->query($sql);
        
        $invoices = array();
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        
        return $invoices;
    }
    
    /**
     * Get invoice by ID with sale items
     * 
     * @param int $invoiceID Invoice ID
     * @return array|null Invoice data with items or null
     */
    public function getInvoiceByID($invoiceID) {
        $conn = $this->db->getConnection();
        
        // Get invoice details
        $sql = "SELECT i.*, u.username 
                FROM invoice i 
                LEFT JOIN user u ON i.userID = u.userID 
                WHERE i.invoiceID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $invoiceID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt->close();
            return null;
        }
        
        $invoice = $result->fetch_assoc();
        $stmt->close();
        
        // Get sale items for this invoice
        $saleObj = new Sale();
        $invoice['items'] = $saleObj->getSalesByInvoice($invoiceID);
        
        return $invoice;
    }
    
    /**
     * Delete an invoice and its sale items
     * 
     * @param int $invoiceID Invoice ID
     * @return bool True if deleted successfully
     */
    public function deleteInvoice($invoiceID) {
        if (empty($invoiceID)) {
            return false;
        }
        
        $conn = $this->db->getConnection();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            /**
             * ALGORITHM: Inventory Restoration
             * 
             * Purpose: Restore product quantities when invoice is deleted
             * Process: Delete sales records (triggers inventory restoration in Sale class)
             * Note: This maintains data consistency - deleted sales restore stock
             */
            // Delete all sales for this invoice (will restore inventory)
            $saleObj = new Sale();
            if (!$saleObj->deleteSalesByInvoice($invoiceID)) {
                throw new Exception("Failed to delete sales");
            }
            
            // Delete invoice
            $sql = "DELETE FROM invoice WHERE invoiceID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $invoiceID);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete invoice");
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
    
    /**
     * Get total invoices count
     * 
     * ALGORITHM: Aggregation - COUNT
     * Time Complexity: O(n) where n = number of invoices
     * 
     * @return int Total number of invoices
     */
    public function getTotalInvoices() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM invoice");
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    /**
     * Get invoices by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Array of invoices
     */
    public function getInvoicesByDateRange($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT i.*, u.username 
                FROM invoice i 
                LEFT JOIN user u ON i.userID = u.userID 
                WHERE i.invoiceDate BETWEEN ? AND ? 
                ORDER BY i.invoiceDate DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $invoices = array();
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        
        $stmt->close();
        return $invoices;
    }
    
    /**
     * Export invoice to PDF
     * Matches ExportPDF() from Class Diagram
     * 
     * @param int $invoiceID Invoice ID
     * @return string|bool PDF file path or false
     */
    public function exportPDF($invoiceID) {
        // Get invoice details
        $invoice = $this->getInvoiceByID($invoiceID);
        
        if (!$invoice) {
            return false;
        }
        
        // Generate simple HTML for PDF
        $html = $this->generateInvoiceHTML($invoice);
        
        // For now, just return the HTML
        // In a real system, you'd use a library like TCPDF or mPDF
        return $html;
    }
    
    /**
     * Generate invoice HTML
     * 
     * @param array $invoice Invoice data
     * @return string HTML content
     */
    private function generateInvoiceHTML($invoice) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Invoice #' . $invoice['invoiceID'] . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .invoice-info { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #4CAF50; color: white; }
                .total { text-align: right; font-weight: bold; font-size: 18px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>INVOICE</h1>
                <p>Smart Inventory Management System</p>
            </div>
            
            <div class="invoice-info">
                <p><strong>Invoice #:</strong> ' . $invoice['invoiceID'] . '</p>
                <p><strong>Date:</strong> ' . date('d M Y', strtotime($invoice['invoiceDate'])) . '</p>
                <p><strong>Customer:</strong> ' . htmlspecialchars($invoice['customerName']) . '</p>
                <p><strong>Issued by:</strong> ' . htmlspecialchars($invoice['username']) . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($invoice['items'] as $item) {
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['productCode']) . '</td>
                        <td>' . htmlspecialchars($item['productName']) . '</td>
                        <td>' . $item['quantitySold'] . '</td>
                        <td>RM ' . number_format($item['unitPrice'], 2) . '</td>
                        <td>RM ' . number_format($item['totalPrice'], 2) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="total">
                <p>TOTAL: RM ' . number_format($invoice['totalAmount'], 2) . '</p>
            </div>
            
            <p style="text-align: center; margin-top: 40px; color: #666;">
                Thank you for your business!
            </p>
        </body>
        </html>';
        
        return $html;
    }
}