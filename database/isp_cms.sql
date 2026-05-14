-- ============================================================
-- KigaliNet ISP Customer Management System
-- Database: isp_cms  |  Student ID: 26524
-- ============================================================

CREATE DATABASE IF NOT EXISTS isp_cms;
USE isp_cms;

-- Admins
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    password VARCHAR(255) DEFAULT NULL,
    status ENUM('Active','Suspended','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Service Plans
CREATE TABLE IF NOT EXISTS service_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    speed VARCHAR(30) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT
);

-- Subscriptions
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('Active','Suspended','Cancelled') DEFAULT 'Active',
    start_date DATE NOT NULL,
    end_date DATE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES service_plans(id)
);

-- Invoices
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('Cash','MoMo','Bank Transfer') DEFAULT 'Cash',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Support Tickets
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    issue TEXT NOT NULL,
    status ENUM('Open','In Progress','Resolved') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ---- SEED DATA ----

-- Default admin (username: admin | password: admin123)
INSERT INTO admins (username, password) VALUES
('admin', 'admin123');

-- Service Plans
INSERT INTO service_plans (name, speed, price, description) VALUES
('Basic',    '10 Mbps',  15000, 'Ideal for light browsing and email'),
('Standard', '50 Mbps',  35000, 'Great for streaming and remote work'),
('Premium',  '100 Mbps', 60000, 'Best for heavy usage and gaming'),
('Business', '500 Mbps', 150000,'Dedicated line for businesses');

-- Sample Customers
INSERT INTO customers (name, email, phone, address) VALUES
('Jean Pierre Habimana', 'jp.habimana@email.com', '0788001001', 'Kigali, Gasabo'),
('Marie Claire Uwase',   'mc.uwase@email.com',    '0788001002', 'Kigali, Kicukiro'),
('Eric Nshimiyimana',    'eric.n@email.com',       '0788001003', 'Kigali, Nyarugenge');

-- Sample Subscriptions
INSERT INTO subscriptions (customer_id, plan_id, status, start_date) VALUES
(1, 2, 'Active', CURDATE()),
(2, 1, 'Active', CURDATE()),
(3, 3, 'Active', CURDATE());

-- Sample Invoices
INSERT INTO invoices (subscription_id, amount, status, due_date) VALUES
(1, 35000, 'Unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 15000, 'Paid',   DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(3, 60000, 'Unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- Sample Payment
INSERT INTO payments (invoice_id, amount, method) VALUES (2, 15000, 'MoMo');

-- Sample Tickets
INSERT INTO support_tickets (customer_id, issue, status) VALUES
(1, 'Internet connection dropping frequently', 'Open'),
(3, 'Slow speeds during evening hours', 'In Progress');
