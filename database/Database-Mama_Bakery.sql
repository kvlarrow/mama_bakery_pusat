-- 1. Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(50) NOT NULL -- contoh: 'admin', 'kasir'
);

-- 2. Users (password menggunakan MD5)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    PASSWORD CHAR(32) NOT NULL, -- MD5 hash
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- 3. Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(100) NOT NULL
);

-- 4. Products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(100) NOT NULL,
    category_id INT,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    photo VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 5. Price History
CREATE TABLE price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    old_price DECIMAL(12,2),
    new_price DECIMAL(12,2),
    changed_by INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- 6. Discounts (opsional)
CREATE TABLE discounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    discount_percent DECIMAL(5,2),
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 7. Payments
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    NAME VARCHAR(50) NOT NULL -- contoh: 'Cash', 'QRIS', 'Transfer'
);

-- 8. Transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_code VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    payment_id INT,
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2),
    change_amount DECIMAL(12,2),
    STATUS ENUM('draft', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id)
);

-- 9. Transaction Items
CREATE TABLE transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 10. Login Logs (opsional)
CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time DATETIME,
    logout_time DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
