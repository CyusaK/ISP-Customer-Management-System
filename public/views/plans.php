<?php // app/views/plans.php ?>
<div class="section">
  <div class="form-card">
    <h3>Add New Service Plan</h3>
    <form method="POST" action="index.php?page=plans">
      <div class="form-row">
        <div class="form-group"><label>Plan Name</label><input name="name" placeholder="e.g. Premium" required></div>
        <div class="form-group"><label>Speed</label><input name="speed" placeholder="e.g. 100 Mbps" required></div>
        <div class="form-group"><label>Price (RWF/month)</label><input name="price" type="number" step="500" placeholder="35000" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Description</label><input name="description" placeholder="Brief description"></div>
      </div>
      <button type="submit" class="btn btn-primary">Add Plan</button>
    </form>
  </div>

  <div class="section-header"><h2>All Service Plans</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Speed</th><th>Price (RWF)</th><th>Description</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
        $plans = $planCtrl->showPlans();
        $i = 1;
        while ($p = $plans->fetch_assoc()):
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
          <td><?= htmlspecialchars($p['speed']) ?></td>
          <td><?= number_format($p['price']) ?></td>
          <td><?= htmlspecialchars($p['description']) ?></td>
          <td>
            <a href="index.php?page=plans&action=delete&id=<?= $p['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this plan?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
