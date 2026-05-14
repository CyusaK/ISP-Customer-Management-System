<!DOCTYPE html>
<!--
  app/views/layout.php — Shared Admin Layout (View)
  KigaliNet ISP Customer Management System
  Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE

  This file is the master template included by index.php on every request.
  It renders the sidebar navigation, the top bar, flash messages,
  and then dynamically includes the correct module view based on $page.
-->
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KigaliNet ISP — Customer Management System</title>
  <!-- Shared stylesheet for the entire admin panel -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="wrapper">

  <!-- ── Sidebar Navigation ──────────────────────────────────────────────── -->
  <!-- The 'active' class highlights the link for the current page ($page) -->
  <aside class="sidebar">
    <div class="brand">
      <span class="brand-icon"></span>
      <span>KigaliNet ISP</span>
    </div>
    <nav>
      <a href="index.php?page=dashboard"     class="<?= $page==='dashboard'?'active':'' ?>">Dashboard</a>
      <a href="index.php?page=customers"     class="<?= $page==='customers'?'active':'' ?>">Customers</a>
      <a href="index.php?page=plans"         class="<?= $page==='plans'?'active':'' ?>">Service Plans</a>
      <a href="index.php?page=subscriptions" class="<?= $page==='subscriptions'?'active':'' ?>">Subscriptions</a>
      <a href="index.php?page=billing"       class="<?= $page==='billing'?'active':'' ?>">Billing</a>
      <a href="index.php?page=support"       class="<?= $page==='support'?'active':'' ?>">Support Tickets</a>
      <a href="index.php?page=reports"       class="<?= $page==='reports'?'active':'' ?>">Reports</a>
    </nav>

    <!-- Quick link to the customer-facing portal -->
    <div style="padding:14px 22px;">
      <a href="portal.php" style="font-size:12px;color:#9ab0c8;text-decoration:none;">Customer Portal</a>
    </div>

    <!-- Sign Out destroys the admin session and redirects to login.php -->
    <div style="padding:6px 22px 14px;">
      <a href="logout.php" style="font-size:12px;color:#9ab0c8;text-decoration:none;">Sign Out</a>
    </div>

    <div class="sidebar-footer">Student ID: 26524</div>
  </aside>

  <!-- ── Main Content Area ───────────────────────────────────────────────── -->
  <main class="content">

    <!-- Top bar: shows the current page title and today's date -->
    <header class="topbar">
      <h1 class="page-title">
        <?php
          // Map page keys to human-readable titles for the top bar
          $titles = [
              'dashboard'     => 'Dashboard',
              'customers'     => 'Customers',
              'plans'         => 'Service Plans',
              'subscriptions' => 'Subscriptions',
              'billing'       => 'Billing',
              'support'       => 'Support Tickets',
              'reports'       => 'Reports',
          ];
          echo $titles[$page] ?? 'KigaliNet ISP';
        ?>
      </h1>
      <span class="topbar-date"><?= date('D, d M Y') ?></span>
    </header>

    <!-- ── Flash message banner ──────────────────────────────────────────── -->
    <!-- Controllers redirect with ?msg= after a successful action.         -->
    <!-- This block translates the short key into a readable sentence.      -->
    <?php if (!empty($_GET['msg'])): ?>
    <div class="alert alert-success">
      <?php
        $msgs = [
            'registered' => 'Customer registered successfully.',
            'updated'    => 'Record updated successfully.',
            'deleted'    => 'Record deleted.',
            'created'    => 'Created successfully.',
            'generated'  => 'Invoice generated.',
            'paid'       => 'Payment recorded successfully.',
            'submitted'  => 'Support ticket submitted.',
        ];
        echo $msgs[$_GET['msg']] ?? 'Action completed.';
      ?>
    </div>
    <?php endif; ?>

    <!-- ── Dynamic view inclusion ────────────────────────────────────────── -->
    <!-- Map each $page value to its corresponding view file path.          -->
    <!-- Falls back to dashboard if an unknown page is requested.           -->
    <?php
      $viewMap = [
          'customers'     => __DIR__ . '/customers.php',
          'plans'         => __DIR__ . '/plans.php',
          'subscriptions' => __DIR__ . '/subscriptions.php',
          'billing'       => __DIR__ . '/billing.php',
          'support'       => __DIR__ . '/support.php',
          'reports'       => __DIR__ . '/reports.php',
          'dashboard'     => __DIR__ . '/dashboard.php',
      ];
      $viewFile = $viewMap[$page] ?? $viewMap['dashboard'];

      if (file_exists($viewFile)) {
          include $viewFile; // Include the module view inside this layout
      } else {
          echo '<p style="color:red;padding:20px">View not found: ' . htmlspecialchars($viewFile) . '</p>';
      }
    ?>

  </main>
</div>

</body>
</html>
