// ymfbar/kampus_market/kampus_market-0bdaf0d5a808d5a69b67d6f643b0aaae9a1157fd/admin/manage_items.php

<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

// --- MODIFIKASI: Ambil SEMUA item (tidak peduli status) ---
$res = $conn->query("SELECT i.*, u.nama AS seller FROM items i JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC");

// Ambil pesan flash dari session jika ada (dari approve_item.php)
$msg = '';
if (isset($_SESSION['flash_msg_admin'])) {
    $msg = $_SESSION['flash_msg_admin'];
    unset($_SESSION['flash_msg_admin']);
}
?>

<style>
/* ... (CSS sama) ... */

/* Tombol untuk Status Pending/Approve */
.btn-action-approve {
    background-color: #10b981; /* Hijau */
    color: #fff;
    border: 1px solid #10b981;
    transition: background-color 0.2s;
    border-radius: 8px;
    font-weight: 500;
}
.btn-action-approve:hover {
    background-color: #059669;
}
.status-badge {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.8em;
    font-weight: 600;
}
.status-pending {
    background-color: #fee2e2; /* Merah muda */
    color: #dc2626; /* Merah */
}
.status-approved {
    background-color: #d1fae5; /* Hijau muda */
    color: #059669; /* Hijau tua */
}
</style>

<div class="mb-2 text-center">
    <img src="../uploads/logoku.png" 
         alt="Logo"
         style="width:1200px; height:120px; object-fit:contain;">
</div>

<h3>📦 Manage Items</h3>

<?php if($msg): ?>
<div class="alert alert-light border mb-3"><?= $msg ?></div>
<?php endif; ?>

<table class="table minimal-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nama</th>
      <th>Seller</th>
      <th>Harga</th>
      <th>Status</th> <th>Created</th>
      <th>Bukti Tax</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php while($it = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $it['id'] ?></td>
      <td><?= htmlspecialchars($it['nama_barang']) ?></td>
      <td><?= htmlspecialchars($it['seller']) ?></td>
      <td>Rp <?= number_format($it['harga']) ?></td>
      
      <td>
        <span class="status-badge status-<?= $it['status'] ?>">
            <?= strtoupper($it['status']) ?>
        </span>
      </td>
      
      <td><?= date('d M Y', strtotime($it['created_at'])) ?></td>
      <td>
        <?php if ($it['bukti_bayar_tax']): ?>
            <a href="../uploads/<?= htmlspecialchars($it['bukti_bayar_tax']) ?>" 
               target="_blank" 
               class="btn btn-sm btn-action-view">
                Lihat Bukti
            </a>
        <?php else: ?>
            <span class="text-muted">N/A</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if($it['status'] === 'pending'): ?>
            <a href="approve_item.php?id=<?= $it['id'] ?>" 
               class="btn btn-sm btn-action-approve mb-1"
               onclick="return confirm('Setujui item ini?')">
                Approve
            </a>
        <?php endif; ?>

        <a href="../detail.php?id=<?= $it['id'] ?>" class="btn btn-sm btn-action-view">
            Lihat
        </a>
        <a href="delete_items.php?id=<?= $it['id'] ?>" 
           class="btn btn-sm btn-action-delete" 
           onclick="return confirm('Hapus item?')">
            Hapus
        </a>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>