<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

$res = $conn->query("SELECT i.*, u.nama AS seller FROM items i JOIN users u ON i.user_id=u.id ORDER BY i.created_at DESC");
?>

<style>
/* CSS Kustom untuk Tampilan Minimalis, Modern, dan Elegan */
.minimal-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin-top: 20px;
    border: 1px solid #e5e7eb; /* Border tipis */
    border-radius: 12px;
    overflow: hidden; /* Penting untuk border-radius */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.minimal-table thead th {
    background-color: #f9fafb; /* Latar belakang terang untuk header */
    color: #111827; /* Teks gelap */
    font-weight: 600;
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.minimal-table tbody tr {
    transition: background-color 0.3s ease;
}

.minimal-table tbody tr:hover {
    background-color: #f3f4f6; /* Hover minimalis */
}

.minimal-table tbody td {
    padding: 12px 15px;
    border-top: 1px solid #f3f4f6;
    color: #4b5563; /* Teks sedikit abu-abu */
}

/* Tombol Hitam & Putih (Minimalist) */
.btn-action-view {
    background-color: #fff;
    color: #111827;
    border: 1px solid #d1d5db;
    transition: background-color 0.2s, border-color 0.2s;
    border-radius: 8px;
    font-weight: 500;
}
.btn-action-view:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
}

.btn-action-delete {
    background-color: #111827;
    color: #fff;
    border: 1px solid #111827;
    transition: background-color 0.2s, border-color 0.2s;
    border-radius: 8px;
    font-weight: 500;
}
.btn-action-delete:hover {
    background-color: #000;
    border-color: #000;
    color: #fff;
}

/* Customizing Bootstrap .btn-sm */
.btn-sm {
    padding: .3rem .75rem;
    font-size: .875rem;
    line-height: 1.5;
}
</style>

<div class="mb-2 text-center">
    <img src="../uploads/logoku.png" 
         alt="Logo"
         style="width:1200px; height:120px; object-fit:contain;">
</div>

<h3>📦 Manage Items</h3>
<table class="table minimal-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nama</th>
      <th>Seller</th>
      <th>Harga</th>
      <th>Created</th>
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
      <td><?= date('d M Y', strtotime($it['created_at'])) ?></td>
      <td>
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