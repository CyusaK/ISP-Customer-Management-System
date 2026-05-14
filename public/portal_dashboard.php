<?php
// public/portal_dashboard.php — Customer Portal Dashboard
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: portal.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Customer.php';
require_once __DIR__ . '/../app/models/ServicePlan.php';
require_once __DIR__ . '/../app/models/Subscription.php';
require_once __DIR__ . '/../app/models/Invoice.php';
require_once __DIR__ . '/../app/models/SupportTicket.php';

$customerModel     = new Customer($conn);
$planModel         = new ServicePlan($conn);
$subscriptionModel = new Subscription($conn);
$invoiceModel      = new Invoice($conn);
$ticketModel       = new SupportTicket($conn);

$customerId   = $_SESSION['customer_id'];
$customerName = $_SESSION['customer_name'];
$message      = '';
$msgType      = '';

// Handle support ticket submission from the portal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue'])) {
    $issue = trim($_POST['issue'] ?? '');
    if ($issue) {
        // Customer ID comes from the session — no need for a dropdown
        $ticketModel->create($customerId, $issue);
        $message = 'Your support ticket has been submitted. We will get back to you shortly.';
        $msgType = 'success';
    } else {
        $message = 'Please describe your issue before submitting.';
        $msgType = 'error';
    }
}

// Handle plan purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $planId = (int)$_POST['plan_id'];
    $plan   = $planModel->getById($planId);

    if ($plan) {
        // Block re-subscription if the customer already has an active plan at the same price.
        // This prevents paying twice for equivalent packages (e.g. two 35,000 RWF plans).
        $stmt = $conn->prepare(
            "SELECT s.id FROM subscriptions s
             JOIN service_plans p ON s.plan_id = p.id
             WHERE s.customer_id = ? AND p.price = ? AND s.status = 'Active'"
        );
        $stmt->bind_param("id", $customerId, $plan['price']);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            $message = 'You already have an active subscription at this price (' . number_format($plan['price']) . ' RWF/month). Cancel it first before subscribing to an equivalent plan.';
            $msgType = 'error';
        } else {
            if ($subscriptionModel->create($customerId, $planId)) {
                // Get the new subscription id
                $subId = $conn->insert_id;
                $dueDate = date('Y-m-d', strtotime('+30 days'));
                // Generate invoice automatically
                $stmt2 = $conn->prepare(
                    "INSERT INTO invoices (subscription_id, amount, status, due_date) VALUES (?, ?, 'Unpaid', ?)"
                );
                $stmt2->bind_param("ids", $subId, $plan['price'], $dueDate);
                $stmt2->execute();
                $message = 'Successfully subscribed to ' . htmlspecialchars($plan['name']) . '! An invoice has been generated.';
                $msgType = 'success';
            } else {
                $message = 'Subscription failed. Please try again.';
                $msgType = 'error';
            }
        }
    }
}

// Fetch customer's own support tickets
$myTickets = $ticketModel->getByCustomer($customerId);

// Fetch data
$plans = $planModel->getAll();

$subStmt = $conn->prepare(
    "SELECT s.*, p.name AS plan_name, p.speed, p.price
     FROM subscriptions s
     JOIN service_plans p ON s.plan_id = p.id
     WHERE s.customer_id = ?
     ORDER BY s.id DESC"
);
$subStmt->bind_param("i", $customerId);
$subStmt->execute();
$mySubscriptions = $subStmt->get_result();

$invStmt = $conn->prepare(
    "SELECT i.*, p.name AS plan_name
     FROM invoices i
     JOIN subscriptions s ON i.subscription_id = s.id
     JOIN service_plans p ON s.plan_id = p.id
     WHERE s.customer_id = ?
     ORDER BY i.created_at DESC
     LIMIT 10"
);
$invStmt->bind_param("i", $customerId);
$invStmt->execute();
$myInvoices = $invStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KigaliNet ISP — My Portal</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .portal-topbar { background:#0a2540; color:#fff; padding:14px 30px;
                     display:flex; justify-content:space-between; align-items:center; }
    .portal-topbar .brand { font-size:18px; font-weight:700; display:flex; align-items:center; gap:8px; }
    .portal-topbar .user  { font-size:14px; display:flex; align-items:center; gap:16px; }
    .portal-topbar a { color:#9ab0c8; text-decoration:none; font-size:13px; }
    .portal-topbar a:hover { color:#fff; }
    .portal-body { max-width:1100px; margin:0 auto; padding:30px 20px; }
    .section-title { font-size:18px; font-weight:700; color:#0a2540; margin-bottom:16px; }
    .plans-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:36px; }
    .plan-card { background:#fff; border-radius:12px; padding:24px 20px;
                 box-shadow:0 2px 10px rgba(0,0,0,.08); text-align:center;
                 border:2px solid transparent; transition:border-color .2s; }
    .plan-card:hover { border-color:#1a73e8; }
    .plan-card .plan-name  { font-size:17px; font-weight:700; color:#0a2540; margin-bottom:6px; }
    .plan-card .plan-speed { font-size:13px; color:#888; margin-bottom:12px; }
    .plan-card .plan-price { font-size:26px; font-weight:700; color:#1a73e8; margin-bottom:4px; }
    .plan-card .plan-price span { font-size:13px; color:#888; font-weight:400; }
    .plan-card .plan-desc  { font-size:12px; color:#999; margin-bottom:18px; min-height:32px; }
    .plan-card .btn { width:100%; }
    .ticket-form { background:#fff; border-radius:10px; padding:24px 28px;
                   box-shadow:0 1px 6px rgba(0,0,0,.07); margin-bottom:28px; }
    .ticket-form h3 { font-size:15px; font-weight:700; color:#0a2540; margin-bottom:14px; }
    .ticket-form textarea { width:100%; padding:10px 12px; border:1px solid #d0d7de;
                            border-radius:6px; font-size:14px; font-family:inherit;
                            resize:vertical; outline:none; min-height:90px; }
    .ticket-form textarea:focus { border-color:#1a73e8; }
    .welcome-banner { background:linear-gradient(135deg,#0a2540,#1a73e8); color:#fff;
                      border-radius:12px; padding:24px 28px; margin-bottom:28px; }
    .welcome-banner h2 { font-size:20px; margin-bottom:4px; }
    .welcome-banner p  { font-size:14px; opacity:.85; }
  </style>
</head>
<body style="background:#f0f2f5; margin:0;">

<div class="portal-topbar">
  <div class="brand">KigaliNet ISP</div>
  <div class="user">
    <span><?= htmlspecialchars($customerName) ?></span>
    <a href="portal_logout.php">Sign Out →</a>
  </div>
</div>

<div class="portal-body">

  <div class="welcome-banner" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
      <h2>Welcome back, <?= htmlspecialchars(explode(' ', $customerName)[0]) ?>!</h2>
      <p>Browse our internet packages below and subscribe to get connected.</p>
    </div>
    <a href="portal_report.php" class="btn" style="background:#fff;color:#0a2540;font-weight:700;white-space:nowrap;">My Invoice Report</a>
  </div>

  <?php if ($message): ?>
    <div class="<?= $msgType === 'success' ? 'msg-success' : 'msg-error' ?>">
      <?= $msgType === 'success' ? 'Success:' : 'Error:' ?> <?= $message ?>
    </div>
  <?php endif; ?>

  <!-- Available Plans -->
  <div class="section-title">Available Internet Packages</div>
  <div class="plans-grid">
    <?php while ($plan = $plans->fetch_assoc()): ?>
    <div class="plan-card">
      <div class="plan-name"><?= htmlspecialchars($plan['name']) ?></div>
      <div class="plan-speed"><?= htmlspecialchars($plan['speed']) ?></div>
      <div class="plan-price">
        <?= number_format($plan['price']) ?> <span>RWF/month</span>
      </div>
      <div class="plan-desc"><?= htmlspecialchars($plan['description']) ?></div>
      <form method="POST">
        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
        <button type="submit" class="btn btn-primary"
                onclick="return confirm('Subscribe to <?= htmlspecialchars($plan['name']) ?> for <?= number_format($plan['price']) ?> RWF/month?')">
          Subscribe
        </button>
      </form>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- My Subscriptions -->
  <div class="section-title">My Subscriptions</div>
  <div class="table-wrap" style="margin-bottom:28px;">
    <table>
      <thead>
        <tr><th>Plan</th><th>Speed</th><th>Price</th><th>Status</th><th>Start Date</th></tr>
      </thead>
      <tbody>
        <?php if ($mySubscriptions->num_rows === 0): ?>
          <tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">No subscriptions yet. Choose a plan above!</td></tr>
        <?php else: ?>
          <?php while ($sub = $mySubscriptions->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($sub['plan_name']) ?></td>
            <td><?= htmlspecialchars($sub['speed']) ?></td>
            <td><?= number_format($sub['price']) ?> RWF</td>
            <td>
              <span class="badge badge-<?= strtolower($sub['status']) ?>">
                <?= $sub['status'] ?>
              </span>
            </td>
            <td><?= $sub['start_date'] ?></td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- My Invoices -->
  <div class="section-title">My Invoices</div>
  <div class="table-wrap" style="margin-bottom:28px;">
    <table>
      <thead>
        <tr><th>#</th><th>Plan</th><th>Amount</th><th>Status</th><th>Due Date</th></tr>
      </thead>
      <tbody>
        <?php if ($myInvoices->num_rows === 0): ?>
          <tr><td colspan="5" style="text-align:center;color:#888;padding:20px;">No invoices yet.</td></tr>
        <?php else: ?>
          <?php while ($inv = $myInvoices->fetch_assoc()): ?>
          <tr>
            <td>#<?= $inv['id'] ?></td>
            <td><?= htmlspecialchars($inv['plan_name']) ?></td>
            <td><?= number_format($inv['amount']) ?> RWF</td>
            <td>
              <span class="badge badge-<?= strtolower($inv['status']) ?>">
                <?= $inv['status'] ?>
              </span>
            </td>
            <td><?= $inv['due_date'] ?></td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Support Tickets -->
  <div class="section-title">Contact Support</div>

  <!-- Ticket submission form — customer describes their issue and submits -->
  <div class="ticket-form">
    <h3>Submit a New Support Ticket</h3>
    <form method="POST">
      <div style="margin-bottom:12px;">
        <textarea name="issue" placeholder="Describe your issue or concern here..." required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit Ticket</button>
    </form>
  </div>

  <!-- Customer's own ticket history -->
  <div class="section-title" style="margin-top:4px;">My Support Tickets</div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Issue</th><th>Status</th><th>Date Submitted</th></tr>
      </thead>
      <tbody>
        <?php if ($myTickets->num_rows === 0): ?>
          <tr><td colspan="4" style="text-align:center;color:#888;padding:20px;">No tickets submitted yet.</td></tr>
        <?php else: ?>
          <?php $i = 1; while ($t = $myTickets->fetch_assoc()): ?>
            <?php
              // Map status to badge class
              $badge = $t['status'] === 'Open' ? 'open'
                     : ($t['status'] === 'In Progress' ? 'progress' : 'resolved');
            ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($t['issue']) ?></td>
            <td><span class="badge badge-<?= $badge ?>"><?= $t['status'] ?></span></td>
            <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
