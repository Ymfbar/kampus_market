<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

$res = $conn->query("SELECT id,nama,email,role,created_at FROM users ORDER BY created_at DESC");
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

/* Tombol Netral (Hanya Border dan Teks) */
.btn-neutral-action {
    background-color: #fff;
    color: #111827;
    border: 1px solid #d1d5db;
    transition: background-color 0.2s, border-color 0.2s;
    border-radius: 8px;
    font-weight: 500;
}
.btn-neutral-action:hover {
    background-color: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
}

/* Badge role */
.badge-role {
    font-size: 13px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}
.badge-role.user {
    background-color: #e5e7eb;
    color: #4b5563;
}
.badge-role.admin {
    background-color: #10b981; /* Hijau modern */
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

<h3>👤 Manage Users</h3>
<table class="table minimal-table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nama</th>
      <th>Email</th>
      <th>Role</th>
      <th>Bergabung</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
  <?php while($u = $res->fetch_assoc()): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['nama']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td>
        <span class="badge badge-role <?= $u['role'] ?>">
            <?= ucfirst($u['role']) ?>
        </span>
      </td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td>
        <?php if($u['role']!=='admin'): ?>
          <a href="toggle_admin.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-neutral-action">
            Jadikan Admin
          </a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>

<?php include '../includes/footer.php'; ?>