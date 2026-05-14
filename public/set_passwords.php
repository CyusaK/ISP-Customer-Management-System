<?php
// public/set_passwords.php
require_once __DIR__ . '/../config/database.php';

$hash = password_hash('Kigali@2026!', PASSWORD_DEFAULT);
$log  = [];

// --- FIX ADMIN ---
// Delete any existing admin rows and insert a clean one
$conn->query("DELETE FROM admins");
$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES ('admin-k', ?)");
$stmt->bind_param("s", $hash);
$stmt->execute();
$log[] = "Admin deleted and re-created: username=admin-k, password=Kigali@2026!";

// Verify it was saved correctly
$check = $conn->query("SELECT username, password FROM admins WHERE username='admin-k'")->fetch_assoc();
if ($check && password_verify('Kigali@2026!', $check['password'])) {
    $log[] = "Verification PASSED — password_verify() confirms hash is correct.";
} else {
    $log[] = "Verification FAILED — something went wrong.";
}

// --- FIX CUSTOMERS ---
$conn->query("ALTER TABLE customers ADD COLUMN IF NOT EXISTS password VARCHAR(255) DEFAULT NULL");
$result  = $conn->query("SELECT id, name FROM customers WHERE password IS NULL OR password = ''");
$updated = 0;
while ($row = $result->fetch_assoc()) {
    $s = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
    $s->bind_param("si", $hash, $row['id']);
    $s->execute();
    $updated++;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>KigaliNet ISP — Fix Passwords</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5;
           display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .box { background: #fff; border-radius: 12px; padding: 44px 40px;
           max-width: 560px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .logo { font-size: 22px; font-weight: 700; color: #0a2540; margin-bottom: 24px; }
    .ok  { background: #e6f4ea; color: #1e8e3e; border-radius: 8px;
           padding: 16px 18px; margin-bottom: 20px; font-size: 14px; line-height: 2; }
    .btn { display: inline-block; padding: 13px 32px; background: #1a73e8;
           color: #fff; border-radius: 8px; text-decoration: none;
           font-weight: 700; font-size: 15px; margin-top: 8px; margin-right: 8px; }
    .btn:hover { background: #1558b0; }
    .note { margin-top: 16px; font-size: 13px; color: #888; }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">KigaliNet ISP — Password Fix</div>
  <div class="ok">
    <?php foreach ($log as $line): ?>
      <?= htmlspecialchars($line) ?><br>
    <?php endforeach; ?>
  </div>
  <a href="login.php" class="btn">Go to Admin Login</a>
  <a href="portal.php" class="btn" style="background:#0a2540;">Customer Portal</a>
  <p class="note">Run this once, then you may delete this file.</p>
</div>
</body>
</html>
