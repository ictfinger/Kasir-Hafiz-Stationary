-- Database Schema for Kasir Hafiz Stationary
-- Import this to your TiDB / Online MySQL Database

CREATE DATABASE IF NOT EXISTS kasir_hafiz_stationary;
USE kasir_hafiz_stationary;

-- Table: Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_barcode (barcode),
    INDEX idx_category (category)
);

-- Table: Transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    total_items INT NOT NULL,
    payment_amount DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Tunai',
    cashier_name VARCHAR(100) NOT NULL DEFAULT 'Admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_code (transaction_code),
    INDEX idx_created_at (created_at)
);

-- Table: Transaction Items
CREATE TABLE IF NOT EXISTS transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_product_id (product_id)
);

-- Sample Data (Optional)
INSERT INTO products (barcode, name, price, stock, category) VALUES 
('8992388101127', 'Pulpen Standard Hitam', 3000, 100, 'Alat Tulis'),
('8992388101134', 'Buku Tulis 48 Halaman', 5000, 50, 'Buku'),
('8992388101141', 'Penghapus Karet Putih', 2000, 75, 'Alat Tulis'),
('8992388101158', 'Penggaris 30cm', 4000, 60, 'Alat Tulis'),
('8992388101165', 'Pensil 2B', 2500, 120, 'Alat Tulis');
