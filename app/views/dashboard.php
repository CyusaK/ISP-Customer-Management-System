<?php // app/views/dashboard.php ?>
<div class="cards">
  <div class="card blue">
    <div class="card-label">Total Customers</div>
    <div class="card-value"><?= $stats['customers'] ?></div>
    <div class="card-icon"></div>
  </div>
  <div class="card green">
    <div class="card-label">Active Subscriptions</div>
    <div class="card-value"><?= $stats['subscriptions'] ?></div>
    <div class="card-icon"></div>
  </div>
  <div class="card orange">
    <div class="card-label">Unpaid Invoices</div>
    <div class="card-value"><?= $stats['unpaid'] ?></div>
    <div class="card-icon"></div>
  </div>
  <div class="card green">
    <div class="card-label">Total Revenue (RWF)</div>
    <div class="card-value"><?= number_format($stats['revenue']) ?></div>
    <div class="card-icon"></div>
  </div>
  <div class="card red">
    <div class="card-label">Open Tickets</div>
    <div class="card-value"><?= $stats['open_tickets'] ?></div>
    <div class="card-icon"></div>
  </div>
</div>

<div class="recent-section">
  <h2>Recent Customers</h2>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Joined</th></tr></thead>
      <tbody>
      <?php
        try {
            $recent = $conn->query("SELECT * FROM customers ORDER BY id DESC LIMIT 5");
        } catch (Exception $e) { $recent = false; }
        if ($recent && $recent->num_rows > 0):
          while ($r = $recent->fetch_assoc()):
            $badge = strtolower($r['status']);
      ?>
        <tr>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td><span class="badge badge-<?= $badge ?>"><?= $r['status'] ?></span></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>
