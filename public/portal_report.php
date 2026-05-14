<?php
/**
 * public/portal_report.php — Customer Invoice Report with Date Filter
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Allows a logged-in customer to filter and view their invoice history
 * by a specific date range (from date → to date).
 * Also shows summary totals: total invoiced, total paid, total unpaid.
 */

session_start();

// Guard: only logged-in customers can access this page
if (!isset($_SESSION['customer_id'])) {
    header('Location: portal.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Invoice.php';

$invoiceModel = new Invoice($conn);
$customerId   = $_SESSION['customer_id'];
$customerName = $_SESSION['customer_name'];

// Read filter inputs from GET — empty string means no filter applied
$dateFrom = trim($_GET['from'] ?? '');
$dateTo   = trim($_GET['to']   ?? '');

// Validate date format (must be Y-m-d or empty)
if ($dateFrom && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = '';
if ($dateTo   && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = '';

// Fetch filtered invoices for this customer
$invoices = $invoiceModel->getByCustomerFiltered($customerId, $dateFrom, $dateTo);

// Calculate summary totals from the result set
$totalInvoiced = 0;
$totalPaid     = 0;
$totalUnpaid   = 0;
$rows          = []; // Store rows so we can iterate twice (totals + display)

while ($row = $invoices->fetch_assoc()) {
    $rows[]         = $row;
    $totalInvoiced += $row['amount'];
    if ($row['status'] === 'Paid')   $totalPaid   += $row['amount'];
    if ($row['status'] === 'Unpaid') $totalUnpaid += $row['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KigaliNet ISP — My Invoice Report</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { background: #f0f2f5; margin: 0; }

    /* Top navigation bar */
    .portal-topbar { background: #0a2540; color: #fff; padding: 14px 30px;
                     display: flex; justify-content: space-between; align-items: center; }
    .portal-topbar .brand { font-size: 18px; font-weight: 700; }
    .portal-topbar .user  { font-size: 14px; display: flex; align-items: center; gap: 16px; }
    .portal-topbar a { color: #9ab0c8; text-decoration: none; font-size: 13px; }
    .portal-topbar a:hover { color: #fff; }

    .report-body { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }

    /* Page header row */
    .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .report-header h2 { font-size: 20px; font-weight: 700; color: #0a2540; }

    /* Date filter form card */
    .filter-card { background: #fff; border-radius: 10px; padding: 20px 24px;
                   box-shadow: 0 1px 6px rgba(0,0,0,.07); margin-bottom: 24px; }
    .filter-card form { display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
    .filter-card .fg { display: flex; flex-direction: column; gap: 5px; }
    .filter-card label { font-size: 13px; font-weight: 600; color: #555; }
    .filter-card input[type=date] { padding: 9px 12px; border: 1px solid #d0d7de;
                                     border-radius: 6px; font-size: 14px; outline: none; }
    .filter-card input[type=date]:focus { border-color: #1a73e8; }
    .filter-note { font-size: 12px; color: #888; margin-top: 10px; }

    /* Summary stat cards */
    .summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    .sum-card { background: #fff; border-radius: 10px; padding: 18px 20px;
                box-shadow: 0 1px 6px rgba(0,0,0,.07); text-align: center; }
    .sum-card .sum-label { font-size: 13px; color: #888; margin-bottom: 6px; }
    .sum-card .sum-value { font-size: 22px; font-weight: 700; }
    .sum-card.total  .sum-value { color: #0a2540; }
    .sum-card.paid   .sum-value { color: #1e8e3e; }
    .sum-card.unpaid .sum-value { color: #d93025; }

    /* No results message */
    .no-results { text-align: center; padding: 40px; color: #888; font-size: 15px; background: #fff;
                  border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }
  </style>
</head>
<body>

<!-- Top navigation bar -->
<div class="portal-topbar">
  <div class="brand">🌐 KigaliNet ISP</div>
  <div class="user">
    <span><?= htmlspecialchars($customerName) ?></span>
    <a href="portal_dashboard.php">← Back to Dashboard</a>
    <a href="portal_logout.php">Sign Out →</a>
  </div>
</div>

<div class="report-body">

  <div class="report-header">
    <h2>📄 My Invoice Report</h2>
    <!-- Show the active filter range if one is applied -->
    <?php if ($dateFrom || $dateTo): ?>
      <span style="font-size:13px;color:#888;">
        Showing:
        <?= $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'beginning' ?>
        →
        <?= $dateTo   ? date('d M Y', strtotime($dateTo))   : 'today' ?>
      </span>
    <?php endif; ?>
  </div>

  <!-- ── Date Range Filter Form ─────────────────────────────────────────── -->
  <!-- Submits via GET so the filtered URL can be bookmarked or shared      -->
  <div class="filter-card">
    <form method="GET" action="portal_report.php">
      <div class="fg">
        <label>From Date</label>
        <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>">
      </div>
      <div class="fg">
        <label>To Date</label>
        <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>">
      </div>
      <button type="submit" class="btn btn-primary">Filter Report</button>
      <!-- Clear button resets both date fields by loading the page with no params -->
      <a href="portal_report.php" class="btn btn-warning">Clear Filter</a>
    </form>
    <p class="filter-note">Leave both fields empty to see your full invoice history.</p>
  </div>

  <!-- ── Summary Totals ────────────────────────────────────────────────── -->
  <div class="summary-cards">
    <div class="sum-card total">
      <div class="sum-label">Total Invoiced</div>
      <div class="sum-value"><?= number_format($totalInvoiced) ?> RWF</div>
    </div>
    <div class="sum-card paid">
      <div class="sum-label">Total Paid</div>
      <div class="sum-value"><?= number_format($totalPaid) ?> RWF</div>
    </div>
    <div class="sum-card unpaid">
      <div class="sum-label">Total Unpaid</div>
      <div class="sum-value"><?= number_format($totalUnpaid) ?> RWF</div>
    </div>
  </div>

  <!-- ── Invoice Table ─────────────────────────────────────────────────── -->
  <?php if (empty($rows)): ?>
    <div class="no-results">
      No invoices found<?= ($dateFrom || $dateTo) ? ' for the selected period.' : '.' ?>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Plan</th>
          <th>Amount (RWF)</th>
          <th>Status</th>
          <th>Due Date</th>
          <th>Invoice Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $i => $inv): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= htmlspecialchars($inv['plan_name']) ?></td>
          <td><?= number_format($inv['amount']) ?></td>
          <td>
            <!-- Badge colour matches status: green=Paid, red=Unpaid -->
            <span class="badge badge-<?= strtolower($inv['status']) ?>">
              <?= $inv['status'] ?>
            </span>
          </td>
          <td><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
          <td><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
