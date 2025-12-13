<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

$res = $conn->query("SELECT i.*, u.nama AS seller FROM items i JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC");
?>
<h3>Manage Items</h3>
<table class="table">
  <thead><tr><th>ID</th><th>Nama</th><th>Seller</th><th>Harga</th><th>Created</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while($it = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $it['id'] ?></td>
      <td><?= htmlspecialchars($it['nama_barang']) ?></td>
      <td><?= htmlspecialchars($it['seller']) ?></td>
      <td><?= number_format($it['harga']) ?></td>
      <td><?= $it['created_at'] ?></td>
      <td>
        <a href="../detail.php?id=<?= $it['id'] ?>" class="btn btn-sm btn-primary">Lihat</a>
        <a href="delete_item.php?id=<?= $it['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item?')">Hapus</a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>
