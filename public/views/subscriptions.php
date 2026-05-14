<?php // app/views/subscriptions.php ?>
<div class="section">
  <div class="form-card">
    <h3>Create New Subscription</h3>
    <form method="POST" action="index.php?page=subscriptions">
      <div class="form-row">
        <div class="form-group">
          <label>Customer</label>
          <select name="customer_id" required>
            <option value="">-- Select Customer --</option>
            <?php
              $custs = $conn->query("SELECT id, name FROM customers WHERE status='Active' ORDER BY name");
              while ($c = $custs->fetch_assoc()):
            ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Service Plan</label>
          <select name="plan_id" required>
            <option value="">-- Select Plan --</option>
            <?php
              $plns = $conn->query("SELECT id, name, speed, price FROM service_plans ORDER BY price");
              while ($p = $plns->fetch_assoc()):
            ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> — <?= $p['speed'] ?> (<?= number_format($p['price']) ?> RWF)</option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
  </div>

  <div class="section-header"><h2>All Subscriptions</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Customer</th><th>Plan</th><th>Price (RWF)</th><th>Status</th><th>Start Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
        $subs = $subscriptionCtrl->list();
        $i = 1;
        while ($s = $subs->fetch_assoc()):
          $badge = strtolower($s['status']);
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($s['customer_name']) ?></td>
          <td><?= htmlspecialchars($s['plan_name']) ?></td>
          <td><?= number_format($s['price']) ?></td>
          <td><span class="badge badge-<?= $badge ?>"><?= $s['status'] ?></span></td>
          <td><?= $s['start_date'] ?></td>
          <td>
            <?php if ($s['status'] === 'Active'): ?>
            <a href="index.php?page=subscriptions&action=status&id=<?= $s['id'] ?>&status=Suspended" class="btn btn-warning btn-sm">Suspend</a>
            <?php else: ?>
            <a href="index.php?page=subscriptions&action=status&id=<?= $s['id'] ?>&status=Active" class="btn btn-success btn-sm">Activate</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
