<?php
/**
 * public/login.php — Admin Login Page
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Handles admin authentication. On GET it shows the login form.
 * On POST it validates credentials against the admins table using
 * password_verify() (bcrypt), then starts a session on success.
 */

session_start();

// If the admin is already logged in, skip the login page entirely
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

// Load the database connection ($conn)
require_once __DIR__ . '/../config/database.php';

$error = ''; // Will hold any login error message shown to the user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs — trim whitespace to avoid accidental spaces
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    // password_verify() compares the plain-text input against the stored bcrypt hash
    if ($admin && password_verify($password, $admin['password'])) {
        // Store the admin username in the session to mark them as authenticated
        $_SESSION['admin'] = $admin['username'];
        header('Location: index.php'); // Redirect to the dashboard
        exit;
    }

    // Credentials did not match — show a generic error (do not reveal which field was wrong)
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KigaliNet ISP — Admin Login</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a2540;
           display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .card { background: #fff; border-radius: 14px; padding: 48px 40px;
            width: 100%; max-width: 420px; box-shadow: 0 8px 40px rgba(0,0,0,.3); }
    .logo { text-align: center; margin-bottom: 30px; }
    .logo .icon { font-size: 44px; }
    .logo h1 { font-size: 22px; color: #0a2540; margin-top: 8px; font-weight: 700; }
    .logo p  { font-size: 13px; color: #888; margin-top: 4px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600;
                        color: #444; margin-bottom: 6px; }
    .form-group input { width: 100%; padding: 11px 14px; border: 1px solid #d0d7de;
                        border-radius: 8px; font-size: 14px; outline: none; }
    .form-group input:focus { border-color: #1a73e8; }
    .btn { width: 100%; padding: 13px; background: #1a73e8; color: #fff;
           border: none; border-radius: 8px; font-size: 15px;
           font-weight: 700; cursor: pointer; margin-top: 6px; }
    .btn:hover { background: #1558b0; }
    .error { background: #fce8e6; color: #d93025; border-radius: 8px;
             padding: 11px 14px; font-size: 13px; margin-bottom: 18px; }
    .portal-link { text-align: center; margin-top: 20px; font-size: 13px; color: #888; }
    .portal-link a { color: #1a73e8; text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="icon">🌐</div>
    <h1>KigaliNet ISP</h1>
    <p>Admin Portal — Sign In</p>
  </div>

  <?php if ($error): ?>
    <!-- Show the error banner only when login fails -->
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Login form — submits via POST to this same page -->
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input name="username" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input name="password" type="password" required>
    </div>
    <button type="submit" class="btn">Sign In</button>
  </form>

  <!-- Link for customers who landed on the wrong portal -->
  <div class="portal-link">
    Are you a customer? <a href="portal.php">Go to Customer Portal →</a>
  </div>
</div>
</body>
</html>
