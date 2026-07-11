CREATE DATABASE IF NOT EXISTS ledgergo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ledgergo;

-- ============ USERS (Staff/Admin) ============
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firebase_uid VARCHAR(128) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    role ENUM('admin','staff') DEFAULT 'staff',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ SETTINGS ============
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(150) NOT NULL DEFAULT 'My Shop',
    phone VARCHAR(20),
    address VARCHAR(255),
    language ENUM('en','bn') DEFAULT 'en',
    opening_balance DECIMAL(15,2) DEFAULT 0.00,
    logo VARCHAR(255) DEFAULT 'assets/logo.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ CUSTOMERS ============
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    total_due DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_phone (phone)
) ENGINE=InnoDB;

-- ============ SUPPLIERS ============
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    total_due DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_phone (phone)
) ENGINE=InnoDB;

-- ============ CATEGORIES ============
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ PRODUCTS ============
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    buy_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sell_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stock_qty DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    alert_qty DECIMAL(15,2) DEFAULT 5.00,
    unit VARCHAR(30) DEFAULT 'pcs',
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_product_name (name)
) ENGINE=InnoDB;

-- ============ PURCHASES (Master) ============
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    due_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    purchase_date DATE NOT NULL,
    note VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_purchase_date (purchase_date)
) ENGINE=InnoDB;

-- ============ PURCHASE ITEMS ============
CREATE TABLE purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    buy_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ============ SALES (Master) ============
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    due_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_profit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    sale_date DATE NOT NULL,
    note VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_sale_date (sale_date)
) ENGINE=InnoDB;

-- ============ SALE ITEMS ============
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    sell_price DECIMAL(15,2) NOT NULL,
    buy_price DECIMAL(15,2) NOT NULL,
    profit DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ============ CUSTOMER PAYMENTS ============
CREATE TABLE customer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    sale_id INT,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============ SUPPLIER PAYMENTS ============
CREATE TABLE supplier_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    purchase_id INT,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============ EXPENSE CATEGORIES ============
CREATE TABLE expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============ EXPENSES ============
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(150) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    expense_date DATE NOT NULL,
    note VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============ CASH TRANSACTIONS (Core Ledger) ============
CREATE TABLE cash_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('opening','deposit','withdraw','sale','purchase','expense','customer_payment','supplier_payment') NOT NULL,
    direction ENUM('in','out') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference_id INT NULL,          -- sale_id / purchase_id / expense_id etc.
    reference_table VARCHAR(50) NULL,
    note VARCHAR(255),
    transaction_date DATE NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- ============ DEFAULT SEED DATA ============
INSERT INTO settings (shop_name, phone, address, language, opening_balance) 
VALUES ('LedgerGo Shop', '01700000000', 'Dhaka, Bangladesh', 'en', 0.00);

INSERT INTO expense_categories (name) VALUES ('Rent'), ('Utility'), ('Salary'), ('Others');
INSERT INTO categories (name) VALUES ('General');