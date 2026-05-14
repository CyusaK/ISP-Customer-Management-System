<?php // app/views/customers.php
$action = $_GET['action'] ?? '';
?>

<?php if ($action === 'edit' && isset($data)): ?>
<!-- Edit Form -->
<div class="section">
  <div class="form-card">
    <h3>Edit Customer</h3>
    <form method="POST" action="index.php?page=customers&action=edit&id=<?= $data['id'] ?>">
      <div class="form-row">
        <div class="form-group"><label>Full Name</label><input name="name" value="<?= htmlspecialchars($data['name']) ?>" required></div>
        <div class="form-group"><label>Email</label><input name="email" type="email" value="<?= htmlspecialchars($data['email']) ?>" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Phone</label><input name="phone" value="<?= htmlspecialchars($data['phone']) ?>"></div>
        <div class="form-group"><label>Address</label><input name="address" value="<?= htmlspecialchars($data['address']) ?>"></div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach (['Active','Suspended','Inactive'] as $s): ?>
            <option value="<?= $s ?>" <?= $data['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <a href="index.php?page=customers" class="btn btn-warning" style="margin-left:8px">Cancel</a>
    </form>
  </div>
</div>

<?php else: ?>
<!-- Register Form -->
<div class="section">
  <div class="form-card">
    <h3>Register New Customer</h3>
    <?php if (!empty($message)): ?><p style="color:red;margin-bottom:10px"><?= $message ?></p><?php endif; ?>
    <form method="POST" action="index.php?page=customers">
      <div class="form-row">
        <div class="form-group"><label>Full Name</label><input name="name" placeholder="e.g. Jean Pierre" required></div>
        <div class="form-group"><label>Email</label><input name="email" type="email" placeholder="email@example.com" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Phone</label><input name="phone" placeholder="07XXXXXXXX"></div>
        <div class="form-group"><label>Address</label><input name="address" placeholder="Kigali, Gasabo"></div>
      </div>
      <button type="submit" class="btn btn-primary">Register Customer</button>
    </form>
  </div>

  <!-- Customer List -->
  <div class="section-header"><h2>All Customers</h2></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
        $customers = $customerCtrl->list();
        $i = 1;
        while ($c = $customers->fetch_assoc()):
          $badge = strtolower($c['status']);
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['phone']) ?></td>
          <td><?= htmlspecialchars($c['address']) ?></td>
          <td><span class="badge badge-<?= $badge ?>"><?= $c['status'] ?></span></td>
          <td>
            <a href="index.php?page=customers&action=edit&id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="index.php?page=customers&action=delete&id=<?= $c['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Delete this customer?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
