<?php // app/views/billing.php ?>
<div class="section">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

    <!-- Generate Invoice -->
    <div class="form-card">
      <h3>Generate Invoice</h3>
      <form method="POST" action="index.php?page=billing">
        <div class="form-group" style="margin-bottom:12px">
          <label>Subscription</label>
          <select name="subscription_id" required>
            <option value="">-- Select Subscription --</option>
            <?php
              $subs = $conn->query(
                "SELECT s.id, c.name, p.name AS plan, p.price
                 FROM subscriptions s
                 JOIN customers c ON s.customer_id=c.id
                 JOIN service_plans p ON s.plan_id=p.id
                 WHERE s.status='Active'"
              );
              while ($s = $subs->fetch_assoc()):
            ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> — <?= $s['plan'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group" style="margin-bottom:14px">
          <label>Amount (RWF)</label>
          <input name="amount" type="number" step="500" placeholder="35000" required>
        </div>
        <button type="submit" class="btn btn-primary">Generate Invoice</button>
      </form>
    </div>

    <!-- Record Payment -->
    <div class="form-card">
      <h3>Record Payment</h3>
      <form method="POST" action="index.php?page=billing&action=pay">
        <div class="form-group" style="margin-bottom:12px">
          <label>Unpaid Invoice</label>
          <select name="invoice_id" required>
            <option value="">-- Select Invoice --</option>
            <?php
              $invs = $conn->query(
                "SELECT i.id, c.name, i.amount
                 FROM invoices i
                 JOIN subscriptions s ON i.subscription_id=s.id
                 JOIN customers c ON s.customer_id=c.id
                 WHERE i.status='Unpaid'"
              );
              while ($inv = $invs->fetch_assoc()):
            ?>
            <option value="<?= $inv['id'] ?>"><?= htmlspecialchars($inv['name']) ?> — <?= number_format($inv['amount']) ?> RWF</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Amount (RWF)</label>
            <input name="amount" type="number" step="500" required>
          </div>
          <div class="form-group">
            <label>Method</label>
            <select name="method">
              <option>Cash</option><option>MoMo</option><option>Bank Transfer</option>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-success" style="margin-top:8px">Record Payment</button>
      </form>
    </div>
  </div>

  <!-- Invoices Table -->
  <div class="section-header"><h2>All Invoices</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Customer</th><th>Plan</th><th>Amount (RWF)</th><th>Status</th><th>Due Date</th><th>Created</th></tr></thead>
      <tbody>
      <?php
        $invoices = $billingCtrl->listInvoices();
        $i = 1;
        while ($inv = $invoices->fetch_assoc()):
          $badge = strtolower($inv['status']);
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($inv['customer_name']) ?></td>
          <td><?= htmlspecialchars($inv['plan_name']) ?></td>
          <td><?= number_format($inv['amount']) ?></td>
          <td><span class="badge badge-<?= $badge ?>"><?= $inv['status'] ?></span></td>
          <td><?= $inv['due_date'] ?></td>
          <td><?= date('d M Y', strtotime($inv['created_at'])) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
