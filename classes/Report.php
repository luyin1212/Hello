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
     * ALGORITHM: Aggregation - COUNT with Date Range Filter
     * 
     * Purpose: Count invoices within specified date range
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
     * ALGORITHM: Aggregation - SUM with Date Range Filter
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
     * ALGORITHM: Aggregation - SUM with JOIN
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
     * ALGORITHM: Aggregation + Sorting + Limit
     * 
     *  Purpose: Identify best-selling products
     * Process:
     * 1. GROUP BY productID - aggregate sales per product
     * 2. SUM(quantitySold) - total units sold
     * 3. ORDER BY totalSold DESC - sort by sales volume
     * 4. LIMIT - return top N products
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
    
    /**
     * Classify products using ABC Analysis
     * Based on Pareto Principle (80/20 rule) for inventory management
     * 
     * Algorithm Steps:
     * 1. Calculate total revenue per product
     * 2. Sort products by revenue (descending)
     * 3. Calculate cumulative percentage
     * 4. Classify into categories:
     *    - Category A: Products contributing to first 70% of revenue (High priority)
     *    - Category B: Products contributing to next 20% of revenue (Medium priority)
     *    - Category C: Products contributing to last 10% of revenue (Low priority)
     * 
     * Academic Reference:
     * Pareto, V. (1896). Cours d'économie politique. Lausanne: F. Rouge.
     * 
     * Silver, E. A., Pyke, D. F., & Peterson, R. (1998). 
     * Inventory Management and Production Planning and Scheduling (3rd ed.). 
     * John Wiley & Sons.
     * 
     * @return array Products with ABC classification
     */
    public function getABCAnalysis() {
        $conn = $this->db->getConnection();
        
        // Step 1: Get revenue per product from sales data
        $sql = "SELECT p.productID, 
                       p.productCode, 
                       p.name,
                       p.price,
                       COALESCE(SUM(s.totalPrice), 0) as totalRevenue,
                       COALESCE(SUM(s.quantitySold), 0) as totalSold
                FROM product p
                LEFT JOIN sale s ON p.productID = s.productID
                GROUP BY p.productID, p.productCode, p.name, p.price
                ORDER BY totalRevenue DESC";
        
        $result = $conn->query($sql);
        
        if (!$result) {
            return [];
        }
        
        $products = [];
        $totalRevenue = 0;
        
        // Collect all products and calculate total revenue
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
            $totalRevenue += $row['totalRevenue'];
        }
        
        // If no sales data, return empty
        if ($totalRevenue == 0) {
            foreach ($products as &$product) {
                $product['category'] = 'C';
                $product['priority'] = 'Low';
                $product['cumulativePercentage'] = 0;
                $product['revenuePercentage'] = 0;
            }
            return $products;
        }
        
        // Step 2: Calculate cumulative percentage and classify
        $cumulativeRevenue = 0;
        
        foreach ($products as &$product) {
            $productRevenue = $product['totalRevenue'];
            $cumulativeRevenue += $productRevenue;
            
            // Calculate percentage of total revenue
            $revenuePercentage = ($productRevenue / $totalRevenue) * 100;
            $product['revenuePercentage'] = round($revenuePercentage, 2);
            
            // Calculate cumulative percentage
            $cumulativePercentage = ($cumulativeRevenue / $totalRevenue) * 100;
            $product['cumulativePercentage'] = round($cumulativePercentage, 2);
            
            // Step 3: ABC Classification based on Pareto Principle
            if ($cumulativePercentage <= 70) {
                $product['category'] = 'A';
                $product['priority'] = 'High';
                $product['description'] = 'High-value items (Top 70% revenue)';
            } elseif ($cumulativePercentage <= 90) {
                $product['category'] = 'B';
                $product['priority'] = 'Medium';
                $product['description'] = 'Medium-value items (Next 20% revenue)';
            } else {
                $product['category'] = 'C';
                $product['priority'] = 'Low';
                $product['description'] = 'Low-value items (Last 10% revenue)';
            }
        }
        
        return $products;
    }
}