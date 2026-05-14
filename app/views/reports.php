<?php
/**
 * app/views/reports.php — Admin Invoice & Customer Profile Report
 * KigaliNet ISP Customer Management System
 * Student ID: 26524 | Instructor: RUTARINDWA JEAN PIERRE
 *
 * Displays a filterable report of all customer invoices.
 * Admin can filter by date range; dates beyond today are blocked.
 * A "View Full Report" button clears all filters.
 * The page is print-ready (sidebar and controls hidden on print).
 */

// Today's date — used as the upper boundary for both date inputs
$today = date('Y-m-d');

// Read filter inputs from GET
$dateFrom    = trim($_GET['from'] ?? '');
$dateTo      = trim($_GET['to']   ?? '');
$filterError = '';

// Server-side validation: reject invalid format or future dates
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

$isFiltered = ($dateFrom !== '' || $dateTo !== '');

// Build the invoice query with optional date filters
$sql    = "SELECT i.*, c.name AS customer_name, p.name AS plan_name
           FROM invoices i
           JOIN subscriptions s ON i.subscription_id = s.id
           JOIN customers c     ON s.customer_id = c.id
           JOIN service_plans p ON s.plan_id = p.id
           WHERE 1=1";
$params = [];
$types  = '';

if ($dateFrom) { $sql .= " AND DATE(i.created_at) >= ?"; $types .= 's'; $params[] = $dateFrom; }
if ($dateTo)   { $sql .= " AND DATE(i.created_at) <= ?"; $types .= 's'; $params[] = $dateTo;   }
$sql .= " ORDER BY i.created_at DESC";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $invoiceRows = $stmt->get_result();
} else {
    $invoiceRows = $conn->query($sql);
}

// Collect rows and compute totals
$rows          = [];
$totalInvoiced = 0;
$totalPaid     = 0;
$totalUnpaid   = 0;

while ($row = $invoiceRows->fetch_assoc()) {
    $rows[]         = $row;
    $totalInvoiced += $row['amount'];
    if ($row['status'] === 'Paid')   $totalPaid   += $row['amount'];
    if ($row['status'] === 'Unpaid') $totalUnpaid += $row['amount'];
}

// Overall customer summary (unaffected by date filter — always shows totals)
$totals = $conn->query("
    SELECT
        COUNT(DISTINCT c.id)                                              AS total_customers,
        COUNT(DISTINCT CASE WHEN c.status='Active' THEN c.id END)        AS active_customers,
        COALESCE(SUM(i.amount), 0)                                        AS total_invoiced,
        COALESCE(SUM(CASE WHEN i.status='Paid' THEN i.amount END), 0)    AS total_paid
    FROM customers c
    LEFT JOIN subscriptions s ON s.customer_id = c.id
    LEFT JOIN invoices i      ON i.subscription_id = s.id
")->fetch_assoc();
?>

<style>
  /* Hide controls and sidebar when printing */
  @media print {
    .sidebar, .topbar, .btn-print, .alert,
    .filter-card, .no-print { display: none !important; }
    .content { margin-left: 0 !important; }
    body { background: #fff; }
  }
  .report-header { display:flex; justify-content:space-between; align-items:center;
                   padding:0 30px 18px; flex-wrap:wrap; gap:10px; }
  .report-header h2 { font-size:17px; color:#0a2540; font-weight:700; }
  .report-header .header-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }

  /* Summary cards row */
  .summary-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
                   gap:16px; padding:0 30px 24px; }
  .s-card { background:#fff; border-radius:10px; padding:18px 20px;
            box-shadow:0 1px 6px rgba(0,0,0,.07); }
  .s-card .s-label { font-size:12px; color:#888; margin-bottom:6px; }
  .s-card .s-value { font-size:24px; font-weight:700; color:#0a2540; }
  .s-card.blue  .s-value { color:#1a73e8; }
  .s-card.green .s-value { color:#1e8e3e; }

  /* Filter card */
  .filter-card { margin:0 30px 24px; background:#fff; border-radius:10px;
                 padding:20px 24px; box-shadow:0 1px 6px rgba(0,0,0,.07); }
  .filter-card form { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; }
  .filter-card .fg { display:flex; flex-direction:column; gap:5px; }
  .filter-card label { font-size:13px; font-weight:600; color:#555; }
  .filter-card input[type=date] { padding:9px 12px; border:1px solid #d0d7de;
                                   border-radius:6px; font-size:14px; outline:none; }
  .filter-card input[type=date]:focus { border-color:#1a73e8; }
  .filter-note  { font-size:12px; color:#888; margin-top:10px; }
  .filter-error { background:#fce8e6; color:#d93025; border-radius:8px;
                  padding:10px 14px; font-size:13px; margin-top:12px; }
</style>

<!-- Page header -->
<div class="report-header">
  <h2>Invoice Report — <?= $isFiltered
      ? (($dateFrom ? date('d M Y', strtotime($dateFrom)) : 'beginning')
         . ' to '
         . ($dateTo ? date('d M Y', strtotime($dateTo)) : 'today'))
      : date('All records as of d M Y') ?>
  </h2>
  <div class="header-actions no-print">
    <?php if ($isFiltered): ?>
      <a href="index.php?page=reports" class="btn btn-warning">View Full Report</a>
    <?php endif; ?>
    <button class="btn btn-primary" onclick="window.print()">🖨 Print / Save PDF</button>
  </div>
</div>

<!-- Overall summary cards (always show full totals regardless of date filter) -->
<div class="summary-cards">
  <div class="s-card blue">
    <div class="s-label">Total Customers</div>
    <div class="s-value"><?= $totals['total_customers'] ?></div>
  </div>
  <div class="s-card green">
    <div class="s-label">Active Customers</div>
    <div class="s-value"><?= $totals['active_customers'] ?></div>
  </div>
  <div class="s-card">
    <div class="s-label">Total Invoiced (RWF)</div>
    <div class="s-value"><?= number_format($totalInvoiced) ?></div>
  </div>
  <div class="s-card green">
    <div class="s-label">Total Collected (RWF)</div>
    <div class="s-value"><?= number_format($totalPaid) ?></div>
  </div>
  <div class="s-card">
    <div class="s-label">Outstanding (RWF)</div>
    <div class="s-value" style="color:#d93025"><?= number_format($totalUnpaid) ?></div>
  </div>
</div>

<!-- Date Range Filter Form -->
<div class="filter-card no-print">
  <form method="GET" action="index.php">
    <input type="hidden" name="page" value="reports">
    <div class="fg">
      <label>From Date</label>
      <!-- max attribute prevents the browser picker from selecting a future date -->
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
    <a href="index.php?page=reports" class="btn btn-warning">Clear</a>
  </form>
  <p class="filter-note">Leave both fields empty to view all invoices. Dates beyond today are not allowed.</p>
  <?php if ($filterError): ?>
    <div class="filter-error"><?= htmlspecialchars($filterError) ?></div>
  <?php endif; ?>
</div>

<!-- Invoice Table -->
<div class="section">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Plan</th>
          <th>Amount (RWF)</th>
          <th>Status</th>
          <th>Due Date</th>
          <th>Invoice Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="7" style="text-align:center;color:#888;padding:24px;">
              No invoices found<?= $isFiltered ? ' for the selected period.' : '.' ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= htmlspecialchars($row['plan_name']) ?></td>
            <td><?= number_format($row['amount']) ?></td>
            <td>
              <span class="badge badge-<?= strtolower($row['status']) ?>">
                <?= $row['status'] ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($row['due_date'])) ?></td>
            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
