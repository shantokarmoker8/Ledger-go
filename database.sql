-- ================================================
-- Cash Khata Database
-- Import this file in phpMyAdmin (XAMPP)
-- ================================================

CREATE DATABASE IF NOT EXISTS cash_khata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cash_khata;

-- ================================================
-- Users Table (Password Plain Text - as per requirement)
-- ================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User (username: admin / password: admin123)
INSERT INTO users (username, password, full_name) VALUES ('admin', 'admin123', 'Administrator');

-- ================================================
-- Settings Table (Business Info + Cash Balance + Language)
-- ================================================
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(150) DEFAULT 'My Business',
    business_address VARCHAR(255) DEFAULT '',
    business_phone VARCHAR(50) DEFAULT '',
    cash_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    opening_cash_set TINYINT(1) NOT NULL DEFAULT 0,
    language VARCHAR(5) NOT NULL DEFAULT 'en',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO settings (business_name, cash_balance, opening_cash_set, language) VALUES ('My Business', 0, 0, 'en');

-- ================================================
-- Customers Table
-- ================================================
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    address VARCHAR(255) DEFAULT '',
    due DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Suppliers Table
-- ================================================
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(30) NOT NULL,
    address VARCHAR(255) DEFAULT '',
    due DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Products Table (Stock managed here)
-- ================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    purchase_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    sale_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    low_stock_alert INT NOT NULL DEFAULT 5,
    supplier_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- ================================================
-- Purchases Table
-- ================================================
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_id INT DEFAULT NULL,
    quantity INT NOT NULL,
    purchase_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_type ENUM('cash','due') NOT NULL,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    due_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- ================================================
-- Sales Table
-- ================================================
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT DEFAULT NULL,
    quantity INT NOT NULL,
    sale_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    payment_type ENUM('cash','due') NOT NULL,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    due_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- ================================================
-- Expenses Table
-- ================================================
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- Customer Payments Table
-- ================================================
CREATE TABLE customer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ================================================
-- Supplier Payments Table
-- ================================================
CREATE TABLE supplier_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);