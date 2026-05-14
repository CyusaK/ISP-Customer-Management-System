<?php
// app/views/reports.php — Admin: Customer Profile Report | Student ID: 26524

// Fetch all customers with their subscription count, total invoiced, and total paid
$reportQuery = $conn->query("
    SELECT
        c.id, c.name, c.email, c.phone, c.address, c.status, c.created_at,
        COUNT(DISTINCT s.id)                                        AS sub_count,
        COALESCE(SUM(i.amount), 0)                                  AS total_invoiced,
        COALESCE(SUM(CASE WHEN i.status='Paid' THEN i.amount END), 0) AS total_paid
    FROM customers c
    LEFT JOIN subscriptions s ON s.customer_id = c.id
    LEFT JOIN invoices i      ON i.subscription_id = s.id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

// Summary totals
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
  @media print {
    .sidebar, .topbar, .btn-print, .alert { display: none !important; }
    .content { margin-left: 0 !important; }
    body { background: #fff; }
  }
  .report-header { display:flex; justify-content:space-between; align-items:center; padding:0 30px 18px; }
  .report-header h2 { font-size:17px; color:#0a2540; font-weight:700; }
  .summary-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; padding:0 30px 24px; }
  .s-card { background:#fff; border-radius:10px; padding:18px 20px; box-shadow:0 1px 6px rgba(0,0,0,.07); }
  .s-card .s-label { font-size:12px; color:#888; margin-bottom:6px; }
  .s-card .s-value { font-size:24px; font-weight:700; color:#0a2540; }
  .s-card.blue  .s-value { color:#1a73e8; }
  .s-card.green .s-value { color:#1e8e3e; }
</style>

<div class="report-header">
  <h2>Customer Profile Report — <?= date('d M Y') ?></h2>
  <button class="btn btn-primary btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
</div>

<!-- Summary Cards -->
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
    <div class="s-value"><?= number_format($totals['total_invoiced']) ?></div>
  </div>
  <div class="s-card green">
    <div class="s-label">Total Collected (RWF)</div>
    <div class="s-value"><?= number_format($totals['total_paid']) ?></div>
  </div>
</div>

<!-- Customer Profiles Table -->
<div class="section">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          <th>Status</th>
          <th>Subscriptions</th>
          <th>Invoiced (RWF)</th>
          <th>Paid (RWF)</th>
          <th>Outstanding (RWF)</th>
          <th>Joined</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($reportQuery && $reportQuery->num_rows > 0): ?>
          <?php while ($row = $reportQuery->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><span class="badge badge-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
            <td style="text-align:center"><?= $row['sub_count'] ?></td>
            <td><?= number_format($row['total_invoiced']) ?></td>
            <td><?= number_format($row['total_paid']) ?></td>
            <td><?= number_format($row['total_invoiced'] - $row['total_paid']) ?></td>
            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="11" style="text-align:center;color:#888;padding:20px;">No customers found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
