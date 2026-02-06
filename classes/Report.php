<?php
/**
 * Report Generation Class
 * Smart Inventory Management System
 * 
 * Generates various reports including sales reports,
 * inventory reports, and revenue analysis.
 * 
 * Reference:
 * Elmasri, R., & Navathe, S. B. (2015). Fundamentals of Database Systems 
 * (7th ed.). Pearson.
 * - Chapter 8: SQL Aggregate Functions and Grouping
 * - Used for implementing report generation with data aggregation
 */

class Report {
    private $db;
    private $reportID;
    private $type;
    private $period;
    private $totalRevenue;
    
    /**
     * Constructor
     * Initializes database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate sales report
     * Matches GenerateReport() from Class Diagram
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Report data
     */
    public function generateReport($startDate, $endDate) {
        $report = array();
        
        // Basic info
        $report['startDate'] = $startDate;
        $report['endDate'] = $endDate;
        $report['period'] = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
        
        // Sales summary
        $report['totalInvoices'] = $this->getTotalInvoices($startDate, $endDate);
        $report['totalRevenue'] = $this->getTotalRevenue($startDate, $endDate);
        $report['totalItemsSold'] = $this->getTotalItemsSold($startDate, $endDate);
        
        // Top products
        $report['topProducts'] = $this->getTopProducts($startDate, $endDate, 5);
        
        // Daily sales
        $report['dailySales'] = $this->getDailySales($startDate, $endDate);
        
        // Low stock products
        $report['lowStockProducts'] = $this->getLowStockProducts();
        
        return $report;
    }
    
    /**
     * Get total invoices in period
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return int Total invoices
     */
    private function getTotalInvoices($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT COUNT(*) as count FROM invoice 
                WHERE invoiceDate BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'];
    }
    
    /**
     * Get total revenue in period
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return float Total revenue
     */
    private function getTotalRevenue($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT SUM(totalAmount) as total FROM invoice 
                WHERE invoiceDate BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] ?? 0;
    }
    
    /**
     * Get total items sold in period
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return int Total items
     */
    private function getTotalItemsSold($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT SUM(s.quantitySold) as total 
                FROM sale s 
                JOIN invoice i ON s.invoiceID = i.invoiceID 
                WHERE i.invoiceDate BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] ?? 0;
    }
    
    /**
     * Get top selling products
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param int $limit Number of products
     * @return array Top products
     */
    private function getTopProducts($startDate, $endDate, $limit = 5) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT p.name, p.productCode, 
                       SUM(s.quantitySold) as totalSold, 
                       SUM(s.totalPrice) as totalRevenue 
                FROM sale s 
                JOIN product p ON s.productID = p.productID 
                JOIN invoice i ON s.invoiceID = i.invoiceID 
                WHERE i.invoiceDate BETWEEN ? AND ? 
                GROUP BY s.productID 
                ORDER BY totalSold DESC 
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $startDate, $endDate, $limit);
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
     * Get daily sales breakdown
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Daily sales data
     */
    private function getDailySales($startDate, $endDate) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT invoiceDate, 
                       COUNT(*) as invoiceCount, 
                       SUM(totalAmount) as dailyRevenue 
                FROM invoice 
                WHERE invoiceDate BETWEEN ? AND ? 
                GROUP BY invoiceDate 
                ORDER BY invoiceDate ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dailySales = array();
        while ($row = $result->fetch_assoc()) {
            $dailySales[] = $row;
        }
        
        $stmt->close();
        return $dailySales;
    }
    
    /**
     * Get low stock products for report
     * 
     * @return array Low stock products
     */
    private function getLowStockProducts() {
        $sql = "SELECT productCode, name, quantity, reorderLevel 
                FROM product 
                WHERE quantity <= reorderLevel 
                ORDER BY quantity ASC";
        
        $result = $this->db->query($sql);
        
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    /**
     * Export report data
     * Matches ExportReport() from Class Diagram
     * 
     * @param array $report Report data
     * @param string $format Export format (html, csv)
     * @return string Exported content
     */
    public function exportReport($report, $format = 'html') {
        if ($format == 'html') {
            return $this->exportToHTML($report);
        } elseif ($format == 'csv') {
            return $this->exportToCSV($report);
        }
        
        return '';
    }
    
    /**
     * Export report to HTML
     * 
     * @param array $report Report data
     * @return string HTML content
     */
    private function exportToHTML($report) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Sales Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #4CAF50; color: white; }
                .summary { background-color: #f9f9f9; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SALES REPORT</h1>
                <p>Period: ' . $report['period'] . '</p>
            </div>
            
            <div class="summary">
                <h3>Summary</h3>
                <p><strong>Total Invoices:</strong> ' . $report['totalInvoices'] . '</p>
                <p><strong>Total Revenue:</strong> RM ' . number_format($report['totalRevenue'], 2) . '</p>
                <p><strong>Total Items Sold:</strong> ' . $report['totalItemsSold'] . '</p>
            </div>
            
            <h3>Top Products</h3>
            <table>
                <tr>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Quantity Sold</th>
                    <th>Revenue</th>
                </tr>';
        
        foreach ($report['topProducts'] as $product) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($product['productCode']) . '</td>
                    <td>' . htmlspecialchars($product['name']) . '</td>
                    <td>' . $product['totalSold'] . '</td>
                    <td>RM ' . number_format($product['totalRevenue'], 2) . '</td>
                </tr>';
        }
        
        $html .= '
            </table>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Export report to CSV (placeholder)
     * 
     * @param array $report Report data
     * @return string CSV content
     */
    private function exportToCSV($report) {
        // Simplified CSV export
        return "Sales Report - " . $report['period'];
    }
}