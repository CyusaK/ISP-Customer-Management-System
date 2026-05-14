<?php // app/views/support.php ?>
<div class="section">
  <div class="form-card">
    <h3>Submit Support Ticket</h3>
    <form method="POST" action="index.php?page=support">
      <div class="form-row">
        <div class="form-group">
          <label>Customer</label>
          <select name="customer_id" required>
            <option value="">-- Select Customer --</option>
            <?php
              $custs = $conn->query("SELECT id, name FROM customers ORDER BY name");
              while ($c = $custs->fetch_assoc()):
            ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Issue Description</label>
          <input name="issue" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Submit Ticket</button>
    </form>
  </div>

  <div class="section-header"><h2>All Support Tickets</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Customer</th><th>Issue</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
        $tickets = $supportCtrl->list();
        $i = 1;
        while ($t = $tickets->fetch_assoc()):
          $badge = $t['status'] === 'Open' ? 'open' : ($t['status'] === 'In Progress' ? 'progress' : 'resolved');
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($t['customer_name']) ?></td>
          <td><?= htmlspecialchars($t['issue']) ?></td>
          <td><span class="badge badge-<?= $badge ?>"><?= $t['status'] ?></span></td>
          <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
          <td>
            <?php if ($t['status'] === 'Open'): ?>
              <a href="index.php?page=support&action=status&id=<?= $t['id'] ?>&status=In+Progress" class="btn btn-warning btn-sm">In Progress</a>
            <?php elseif ($t['status'] === 'In Progress'): ?>
              <a href="index.php?page=support&action=status&id=<?= $t['id'] ?>&status=Resolved" class="btn btn-success btn-sm">Resolve</a>
            <?php else: ?>
              <span style="color:#888;font-size:12px">Closed</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
