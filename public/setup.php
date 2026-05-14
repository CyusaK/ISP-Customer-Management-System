<?php
// public/setup.php — KigaliNet ISP CMS | One-time database setup
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$errors = [];

// Step 1 — connect WITHOUT selecting a database, create it
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die('<p style="font-family:Arial;color:red;padding:30px">
        Cannot connect to MySQL: ' . $conn->connect_error . '<br><br>
        Make sure <strong>MySQL is running</strong> in XAMPP Control Panel.
    </p>');
}
$conn->query("CREATE DATABASE IF NOT EXISTS isp_cms");
$conn->select_db("isp_cms");

// Step 2 — drop old tables (clean slate) then recreate in correct FK order
$statements = [

    // Drop in reverse FK order to avoid constraint errors
    "DROP TABLE IF EXISTS support_tickets",
    "DROP TABLE IF EXISTS payments",
    "DROP TABLE IF EXISTS invoices",
    "DROP TABLE IF EXISTS subscriptions",
    "DROP TABLE IF EXISTS service_plans",
    "DROP TABLE IF EXISTS customers",
    "DROP TABLE IF EXISTS admins",

    // Recreate in correct order (parents before children)
    "CREATE TABLE admins (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE customers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        address TEXT,
        password VARCHAR(255) DEFAULT NULL,
        status ENUM('Active','Suspended','Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE service_plans (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(80) NOT NULL,
        speed VARCHAR(30) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        description TEXT
    ) ENGINE=InnoDB",

    "CREATE TABLE subscriptions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT NOT NULL,
        plan_id INT NOT NULL,
        status ENUM('Active','Suspended','Cancelled') DEFAULT 'Active',
        start_date DATE NOT NULL,
        end_date DATE,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES service_plans(id)
    ) ENGINE=InnoDB",

    "CREATE TABLE invoices (
        id INT PRIMARY KEY AUTO_INCREMENT,
        subscription_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
        due_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE payments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        invoice_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        method ENUM('Cash','MoMo','Bank Transfer') DEFAULT 'Cash',
        paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE support_tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT NOT NULL,
        issue TEXT NOT NULL,
        status ENUM('Open','In Progress','Resolved') DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // Seed data — ignore duplicates silently
    "INSERT IGNORE INTO admins (username, password) VALUES ('admin-k', '" . password_hash('Kigali@2026!', PASSWORD_DEFAULT) . "')",

    "INSERT IGNORE INTO service_plans (name, speed, price, description) VALUES
        ('Basic',    '10 Mbps',  15000, 'Ideal for light browsing and email'),
        ('Standard', '50 Mbps',  35000, 'Great for streaming and remote work'),
        ('Premium',  '100 Mbps', 60000, 'Best for heavy usage and gaming'),
        ('Business', '500 Mbps', 150000,'Dedicated line for businesses')",

    "INSERT IGNORE INTO customers (name, email, phone, address, password) VALUES
        ('Jean Pierre Habimana', 'jp.habimana@email.com', '0788001001', 'Kigali, Gasabo',    '" . password_hash('Kigali@2026!', PASSWORD_DEFAULT) . "'),
        ('Marie Claire Uwase',   'mc.uwase@email.com',    '0788001002', 'Kigali, Kicukiro',  '" . password_hash('Kigali@2026!', PASSWORD_DEFAULT) . "'),
        ('Eric Nshimiyimana',    'eric.n@email.com',      '0788001003', 'Kigali, Nyarugenge','" . password_hash('Kigali@2026!', PASSWORD_DEFAULT) . "')",

    "INSERT IGNORE INTO subscriptions (customer_id, plan_id, status, start_date) VALUES
        (1, 2, 'Active', CURDATE()),
        (2, 1, 'Active', CURDATE()),
        (3, 3, 'Active', CURDATE())",

    "INSERT IGNORE INTO invoices (subscription_id, amount, status, due_date) VALUES
        (1, 35000, 'Unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
        (2, 15000, 'Paid',   DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
        (3, 60000, 'Unpaid', DATE_ADD(CURDATE(), INTERVAL 30 DAY))",

    "INSERT IGNORE INTO payments (invoice_id, amount, method) VALUES (2, 15000, 'MoMo')",

    "INSERT IGNORE INTO support_tickets (customer_id, issue, status) VALUES
        (1, 'Internet connection dropping frequently', 'Open'),
        (3, 'Slow speeds during evening hours', 'In Progress')",
];

foreach ($statements as $sql) {
    try {
        $conn->query($sql);
    } catch (mysqli_sql_exception $e) {
        $errors[] = $e->getMessage();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>KigaliNet ISP — Setup</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5;
           display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .box { background: #fff; border-radius: 12px; padding: 44px 40px;
           max-width: 520px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .logo { font-size: 22px; font-weight: 700; color: #0a2540; margin-bottom: 24px; }
    .ok  { background: #e6f4ea; color: #1e8e3e; border-radius: 8px;
           padding: 16px 18px; margin-bottom: 20px; font-size: 15px; line-height: 1.8; }
    .err { background: #fce8e6; color: #d93025; border-radius: 8px;
           padding: 14px 18px; margin-bottom: 20px; font-size: 14px; line-height: 1.6; }
    .btn { display: inline-block; padding: 13px 32px; background: #1a73e8;
           color: #fff; border-radius: 8px; text-decoration: none;
           font-weight: 700; font-size: 15px; margin-top: 8px; }
    .btn:hover { background: #1558b0; }
    .note { margin-top: 16px; font-size: 13px; color: #888; }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">KigaliNet ISP — Database Setup</div>

  <?php if (empty($errors)): ?>
    <div class="ok">
      Setup complete! <strong>Setup complete!</strong><br>
      Database <strong>isp_cms</strong> is ready.<br>
      All tables created and sample data loaded.
    </div>
    <a href="index.php" class="btn">Go to Dashboard →</a>
    <p class="note">Setup complete. You may now use the system.</p>

  <?php else: ?>
    <div class="err">
      Setup encountered errors: <strong>Setup encountered errors:</strong><br><br>
      <?php foreach ($errors as $e): ?>
        • <?= htmlspecialchars($e) ?><br>
      <?php endforeach; ?>
    </div>
    <p class="note">Fix the errors above and refresh this page.</p>
  <?php endif; ?>

</div>
</body>
</html>
