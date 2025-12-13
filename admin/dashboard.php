<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }
?>
<h3>Admin Dashboard</h3>

<div class="row">
  <div class="col-md-4">
    <div class="card p-3">
      <h5>Manage Users</h5>
      <a href="manage_users.php" class="btn btn-sm btn-primary">Buka</a>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3">
      <h5>Manage Items</h5>
      <a href="manage_items.php" class="btn btn-sm btn-primary">Buka</a>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
