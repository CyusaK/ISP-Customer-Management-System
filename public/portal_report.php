<?php
/**
 * public/portal_report.php — Customer Invoice Report with Date Filter
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Allows a logged-in customer to filter their invoice history by date range.
 * Dates beyond today are rejected both in the browser (max attribute) and
 * on the server. A "View Full Report" button clears all filters.
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

// Today's date — used as the upper boundary for both inputs
$today = date('Y-m-d');

// Read filter inputs from GET — empty string means no filter applied
$dateFrom = trim($_GET['from'] ?? '');
$dateTo   = trim($_GET['to']   ?? '');

$filterError = '';

// Validate format (Y-m-d) and reject any future date
if ($dateFrom) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $dateFrom = '';
    } elseif ($dateFrom > $today) {
        $filterError = 'The "From" date cannot be in the future.';
        $dateFrom = '';
    }
}
if ($dateTo) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $dateTo = '';
    } elseif ($dateTo > $today) {
        $filterError = 'The "To" date cannot be in the future.';
        $dateTo = '';
    }
}

// Fetch filtered invoices for this customer
$invoices = $invoiceModel->getByCustomerFiltered($customerId, $dateFrom, $dateTo);

// Build summary totals and store rows for the table
$totalInvoiced = 0;
$totalPaid     = 0;
$totalUnpaid   = 0;
$rows          = [];

while ($row = $invoices->fetch_assoc()) {
    $rows[]         = $row;
    $totalInvoiced += $row['amount'];
    if ($row['status'] === 'Paid')   $totalPaid   += $row['amount'];
    if ($row['status'] === 'Unpaid') $totalUnpaid += $row['amount'];
}

// Determine whether a filter is currently active
$isFiltered = ($dateFrom !== '' || $dateTo !== '');
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

    @media print {
      .portal-topbar, .filter-card, .no-print { display: none !important; }
      body { background: #fff; }
    }

    .portal-topbar { background: #0a2540; color: #fff; padding: 14px 30px;
                     display: flex; justify-content: space-between; align-items: center;
                     position: sticky; top: 0; z-index: 100; }
    .portal-topbar .brand { font-size: 18px; font-weight: 700; }
    .portal-topbar .user  { font-size: 14px; display: flex; align-items: center; gap: 16px; }
    .portal-topbar a { color: #9ab0c8; text-decoration: none; font-size: 13px; }
    .portal-topbar a:hover { color: #fff; }

    .report-body { max-width: 1000px; margin: 0 auto; padding: 30px 20px; }

    .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px; }
    .report-header h2 { font-size: 20px; font-weight: 700; color: #0a2540; }

    /* Filter card */
    .filter-card { background: #fff; border-radius: 10px; padding: 20px 24px;
                   box-shadow: 0 1px 6px rgba(0,0,0,.07); margin-bottom: 24px; }
    .filter-card form { display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
    .filter-card .fg { display: flex; flex-direction: column; gap: 5px; }
    .filter-card label { font-size: 13px; font-weight: 600; color: #555; }
    .filter-card input[type=date] { padding: 9px 12px; border: 1px solid #d0d7de;
                                     border-radius: 6px; font-size: 14px; outline: none; }
    .filter-card input[type=date]:focus { border-color: #1a73e8; }
    .filter-note { font-size: 12px; color: #888; margin-top: 10px; }
    .filter-error { background: #fce8e6; color: #d93025; border-radius: 8px;
                    padding: 10px 14px; font-size: 13px; margin-top: 12px; }

    /* Summary cards */
    .summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    .sum-card { background: #fff; border-radius: 10px; padding: 18px 20px;
                box-shadow: 0 1px 6px rgba(0,0,0,.07); text-align: center; }
    .sum-card .sum-label { font-size: 13px; color: #888; margin-bottom: 6px; }
    .sum-card .sum-value { font-size: 22px; font-weight: 700; }
    .sum-card.total  .sum-value { color: #0a2540; }
    .sum-card.paid   .sum-value { color: #1e8e3e; }
    .sum-card.unpaid .sum-value { color: #d93025; }

    .no-results { text-align: center; padding: 40px; color: #888; font-size: 15px;
                  background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,.07); }
  </style>
</head>
<body>

<div class="portal-topbar">
  <div class="brand">KigaliNet ISP</div>
  <div class="user">
    <span><?= htmlspecialchars($customerName) ?></span>
    <a href="portal_dashboard.php">Back to Dashboard</a>
    <a href="portal_logout.php">Sign Out</a>
  </div>
</div>

<div class="report-body">

  <div class="report-header">
    <h2>My Invoice Report</h2>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <!-- Show the active period label when a filter is applied -->
      <?php if ($isFiltered): ?>
        <span style="font-size:13px;color:#888;">
          Showing:
          <?= $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'beginning' ?>
          to
          <?= $dateTo   ? date('d M Y', strtotime($dateTo))   : 'today' ?>
        </span>
      <?php endif; ?>
      <!-- View Full Report clears all filters -->
      <?php if ($isFiltered): ?>
        <a href="portal_report.php" class="btn btn-warning">View Full Report</a>
      <?php endif; ?>
      <button class="btn btn-primary" onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
  </div>

  <!-- Date Range Filter Form -->
  <!-- Uses GET so the filtered URL is bookmarkable -->
  <!-- max="<?= $today ?>" prevents the browser date picker from going beyond today -->
  <div class="filter-card">
    <form method="GET" action="portal_report.php">
      <div class="fg">
        <label>From Date</label>
        <input type="date" name="from"
               value="<?= htmlspecialchars($dateFrom) ?>"
               max="<?= $today ?>">
      </div>
      <div class="fg">
        <label>To Date</label>
        <input type="date" name="to"
               value="<?= htmlspecialchars($dateTo) ?>"
               max="<?= $today ?>">
      </div>
      <button type="submit" class="btn btn-primary">Filter Report</button>
      <a href="portal_report.php" class="btn btn-warning">Clear</a>
    </form>
    <p class="filter-note">Leave both fields empty to view your full invoice history. Dates beyond today are not allowed.</p>
    <?php if ($filterError): ?>
      <div class="filter-error"><?= htmlspecialchars($filterError) ?></div>
    <?php endif; ?>
  </div>

  <!-- Summary Totals -->
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

  <!-- Invoice Table -->
  <?php if (empty($rows)): ?>
    <div class="no-results">
      No invoices found<?= $isFiltered ? ' for the selected period.' : '.' ?>
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
