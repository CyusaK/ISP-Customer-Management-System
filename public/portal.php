<?php
/**
 * public/portal.php — Customer Portal (Login & Registration)
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * This page serves two purposes controlled by ?mode=login|register:
 *   - Login: verifies email + password, starts a customer session
 *   - Register: creates a new customer account with a hashed password
 */

session_start();

// If the customer is already logged in, skip this page and go to their dashboard
if (isset($_SESSION['customer_id'])) {
    header('Location: portal_dashboard.php');
    exit;
}

// Load database connection and the Customer model
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Customer.php';

$customerModel = new Customer($conn);
$error   = ''; // Error message shown in red
$success = ''; // Success message shown in green
$mode    = $_GET['mode'] ?? 'login'; // Default tab is login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Registration branch ───────────────────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Collect and sanitize registration fields
        $name     = trim($_POST['name']             ?? '');
        $email    = trim($_POST['email']            ?? '');
        $phone    = trim($_POST['phone']            ?? '');
        $address  = trim($_POST['address']          ?? '');
        $password = $_POST['password']              ?? '';
        $confirm  = $_POST['confirm_password']      ?? '';

        // Validate required fields
        if (!$name || !$email || !$password) {
            $error = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Ensure the email is in a valid format (e.g. user@domain.com)
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            // Enforce a minimum password length for basic security
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            // Both password fields must match before saving
            $error = 'Passwords do not match.';
        } elseif ($customerModel->findByEmail($email)) {
            // Prevent duplicate accounts — each email must be unique
            $error = 'An account with this email already exists.';
        } else {
            // All validations passed — save the new customer with a hashed password
            if ($customerModel->registerWithPassword($name, $email, $phone, $address, $password)) {
                $success = 'Account created! You can now log in.';
                $mode = 'login'; // Switch the tab to login after successful registration
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }

    // ── Login branch ──────────────────────────────────────────────────────────
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        // Look up the customer record by email address
        $customer = $customerModel->findByEmail($email);

        // Verify the password against the stored bcrypt hash
        if ($customer && $customer['password'] && password_verify($password, $customer['password'])) {
            // Block suspended accounts from logging in
            if ($customer['status'] === 'Suspended') {
                $error = 'Your account is suspended. Please contact support.';
            } else {
                // Store customer identity in the session
                $_SESSION['customer_id']   = $customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                header('Location: portal_dashboard.php');
                exit;
            }
        } else {
            // Generic message — do not reveal whether email or password was wrong
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KigaliNet ISP — Customer Portal</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a2540;
           display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
    .card { background: #fff; border-radius: 14px; padding: 44px 40px;
            width: 100%; max-width: 440px; box-shadow: 0 8px 40px rgba(0,0,0,.3); }
    .logo { text-align: center; margin-bottom: 28px; }
    .logo .icon { font-size: 44px; }
    .logo h1 { font-size: 22px; color: #0a2540; margin-top: 8px; font-weight: 700; }
    .logo p  { font-size: 13px; color: #888; margin-top: 4px; }
    .tabs { display: flex; border-bottom: 2px solid #e8edf2; margin-bottom: 24px; }
    .tab { flex: 1; text-align: center; padding: 10px; font-size: 14px; font-weight: 600;
           color: #888; cursor: pointer; text-decoration: none; }
    .tab.active { color: #1a73e8; border-bottom: 2px solid #1a73e8; margin-bottom: -2px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .form-group input { width: 100%; padding: 10px 13px; border: 1px solid #d0d7de;
                        border-radius: 8px; font-size: 14px; outline: none; }
    .form-group input:focus { border-color: #1a73e8; }
    .btn { width: 100%; padding: 12px; background: #1a73e8; color: #fff;
           border: none; border-radius: 8px; font-size: 15px;
           font-weight: 700; cursor: pointer; margin-top: 4px; }
    .btn:hover { background: #1558b0; }
    .error   { background: #fce8e6; color: #d93025; border-radius: 8px; padding: 11px 14px; font-size: 13px; margin-bottom: 16px; }
    .success { background: #e6f4ea; color: #1e8e3e; border-radius: 8px; padding: 11px 14px; font-size: 13px; margin-bottom: 16px; }
    .admin-link { text-align: center; margin-top: 18px; font-size: 13px; color: #888; }
    .admin-link a { color: #1a73e8; text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="icon"></div>
    <h1>KigaliNet ISP</h1>
    <p>Customer Portal</p>
  </div>

  <!-- Tab switcher: Sign In / Create Account -->
  <div class="tabs">
    <a href="portal.php?mode=login"    class="tab <?= $mode==='login'?'active':'' ?>">Sign In</a>
    <a href="portal.php?mode=register" class="tab <?= $mode==='register'?'active':'' ?>">Create Account</a>
  </div>

  <!-- Display error or success feedback messages -->
  <?php if ($error):   ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <?php if ($mode === 'login'): ?>
  <!-- ── Login form ── -->
  <form method="POST">
    <input type="hidden" name="action" value="login">
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Sign In</button>
  </form>

  <?php else: ?>
  <!-- ── Registration form ── -->
  <form method="POST">
    <input type="hidden" name="action" value="register">
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" required autofocus>
    </div>
    <div class="form-group">
      <label>Email Address</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Phone Number</label>
      <input type="text" name="phone">
    </div>
    <div class="form-group">
      <label>Address</label>
      <input type="text" name="address">
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>
    </div>
    <button type="submit" class="btn">Create Account</button>
  </form>
  <?php endif; ?>

  <!-- Link for admins who landed on the wrong portal -->
  <div class="admin-link">
    Admin? <a href="login.php">Go to Admin Portal →</a>
  </div>
</div>
</body>
</html>
