<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

$res = $conn->query("SELECT id,nama,email,role,created_at FROM users ORDER BY created_at DESC");
?>
<h3>Manage Users</h3>
<table class="table">
  <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while($u = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['nama']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= $u['role'] ?></td>
      <td>
        <?php if($u['role']!=='admin'): ?>
          <a href="toggle_admin.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">Jadikan Admin</a>
        <?php else: ?>
          <span class="badge bg-success">Admin</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>
