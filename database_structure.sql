-- ------------------------------------------------------------------
-- Smart Inventory Management System
-- Database Structure Definition
-- 
-- Implements normalized relational database design with foreign key
-- constraints to maintain referential integrity.
-- 
-- Database Design Reference:
-- Elmasri, R., & Navathe, S. B. (2015). Fundamentals of Database 
-- Systems (7th ed.). Pearson Education.
-- ------------------------------------------------------------------

USE `smart_inventory_v2`;

-- --------------------------------------------------------
-- Table: user
-- Stores user login information
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Username: admin
-- Password: admin123
INSERT INTO `user` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- --------------------------------------------------------
-- Table: product
-- Stores all product information
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product` (
  `productID` int(11) NOT NULL AUTO_INCREMENT,
  `productCode` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `reorderLevel` int(11) DEFAULT 10,
  PRIMARY KEY (`productID`),
  UNIQUE KEY `productCode` (`productCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: invoice
-- Stores invoice header information
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice` (
  `invoiceID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `invoiceDate` date DEFAULT NULL,
  `totalAmount` decimal(10,2) DEFAULT 0.00,
  `customerName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`invoiceID`),
  KEY `userID` (`userID`),
  FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: sale
-- Stores individual items in each invoice
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sale` (
  `saleID` int(11) NOT NULL AUTO_INCREMENT,
  `invoiceID` int(11) DEFAULT NULL,
  `productID` int(11) DEFAULT NULL,
  `quantitySold` int(11) DEFAULT NULL,
  `saleDate` date DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`saleID`),
  KEY `invoiceID` (`invoiceID`),
  KEY `productID` (`productID`),
  FOREIGN KEY (`invoiceID`) REFERENCES `invoice` (`invoiceID`) ON DELETE CASCADE,
  FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: alert
-- Stores low stock alerts
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alert` (
  `alertID` int(11) NOT NULL AUTO_INCREMENT,
  `productID` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `alertDate` date DEFAULT NULL,
  PRIMARY KEY (`alertID`),
  KEY `productID` (`productID`),
  FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: report
-- Stores generated reports
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `report` (
  `reportID` int(11) NOT NULL AUTO_INCREMENT,
  `period` varchar(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `totalRevenue` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`reportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: backup (NEW - was missing before!)
-- Stores database backup information
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `backup` (
  `backupID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `backupDate` datetime DEFAULT NULL,
  `filePath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`backupID`),
  KEY `userID` (`userID`),
  FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- All tables created successfully!
-- --------------------------------------------------------