<?php 
// BLOK LOGIKA HARUS DI ATAS SEMUA INCLUDE UNTUK MENCEGAH 'HEADERS ALREADY SENT'
session_start();
include 'includes/config.php';

// Cek otentikasi
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user']['id'];
$user = [];
if($uid > 0) {
    $stmt = $conn->prepare("SELECT nama, email, no_telp, foto FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
}

// Handle update profile (LOGIKA POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);

    $foto_name = $user['foto'];
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allow = ['jpg','jpeg','png','webp'];
        if(in_array(strtolower($ext), $allow)){
            // Hapus foto lama jika ada dan berbeda
            if($user['foto'] && $user['foto'] !== 'student.png' && file_exists('uploads/'.$user['foto'])) {
                @unlink('uploads/'.$user['foto']);
            }
            $foto_name = uniqid('avatar_').'.'.$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/'.$foto_name);
        }
    }

    // Cek apakah ada perubahan
    $isChanged = ($nama !== $user['nama'] || $email !== $user['email'] || $no_telp !== $user['no_telp'] || (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK));

    if (!$isChanged) {
        $_SESSION['flash_msg'] = 'Tidak ada perubahan yang dilakukan';
        header("Location: profile.php");
        exit;
    }

    $stmt_update = $conn->prepare("UPDATE users SET nama=?, email=?, no_telp=?, foto=? WHERE id=?");
    $stmt_update->bind_param('ssssi', $nama, $email, $no_telp, $foto_name, $uid);

    if ($stmt_update->execute()) {
        $_SESSION['flash_msg'] = 'Profile berhasil diperbarui';
        $_SESSION['user']['nama'] = $nama;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['foto'] = $foto_name; // Update foto di session
        header("Location: profile.php"); 
        exit;
    }
}


// NEW LOGIC: Handle delete photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

    $uid = $_SESSION['user']['id'];
    
    // Ambil nama foto saat ini
    $stmt_fetch = $conn->prepare("SELECT foto FROM users WHERE id=? LIMIT 1");
    $stmt_fetch->bind_param('i', $uid);
    $stmt_fetch->execute();
    $res_fetch = $stmt_fetch->get_result();
    $current_user = $res_fetch->fetch_assoc();
    $old_foto = $current_user['foto'];
    
    // Hanya hapus jika foto ada dan bukan placeholder
    if(!empty($old_foto) && $old_foto !== 'student.png' && file_exists('uploads/'.$old_foto)) {
        @unlink('uploads/'.$old_foto);
    }
    
    // Update DB: Set foto ke string kosong
    $empty_string = '';
    $stmt_delete = $conn->prepare("UPDATE users SET foto=? WHERE id=?");
    $stmt_delete->bind_param('si', $empty_string, $uid);
    
    if ($stmt_delete->execute()) {
        $_SESSION['flash_msg'] = 'Foto profil berhasil dihapus';
        $_SESSION['user']['foto'] = $empty_string; // Update session
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['flash_msg'] = 'Gagal menghapus foto profil';
        header("Location: profile.php");
        exit;
    }
}
?>

<?php 
// Panggil header HANYA setelah semua logika redirect selesai
include 'includes/header.php'; 

// Ambil data user lagi (atau gunakan data dari atas)
$uid = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT nama, email, no_telp, foto FROM users WHERE id=? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// FLASH MESSAGE (MODIFIKASI: Menggunakan alert-light dan text-dark)
$msg = '';
if (isset($_SESSION['flash_msg'])) {
    $msg = '<div class="alert alert-light border text-dark flash-msg">'.$_SESSION['flash_msg'].'</div>';
    unset($_SESSION['flash_msg']);
}


// MODIFIKASI: Ambil barang user berdasarkan status
// Query untuk mengambil item yang di-approve
$approvedItemsResult = $conn->query("
    SELECT * FROM items 
    WHERE user_id = $uid AND status = 'approved'
    ORDER BY created_at DESC
");

// Query baru untuk mengambil item yang sedang pending
$pendingItemsResult = $conn->query("
    SELECT * FROM items 
    WHERE user_id = $uid AND status = 'pending'
    ORDER BY created_at DESC
");
?>

<style>
.profile-card{
    background:#ffffff;
    border-radius:16px;
    border:1px solid #e5e7eb;
}
.section-title{
    font-weight:600;
    letter-spacing:.2px;
}
/* Style untuk konsistensi product card (mirip search.php/index.php) */
.product-card{
    transition: all .25s ease;
    position: relative; 
    border-radius:14px;
    border:1px solid #e5e7eb; /* Default border */
}
.product-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 18px 35px rgba(0,0,0,.1) !important; 
}

/* Modifikasi untuk item yang Approved agar menggunakan border standar */
.product-card.approved-item {
    border: 1px solid #e5e7eb; /* Border abu-abu standar */
}

/* Pertahankan border warning untuk item Pending */

.btn-neutral{
    background:#111827;
    color:#fff;
    border-radius:999px;
}
.btn-neutral:hover{
    background:#000;
}
.text-muted-small{
    font-size:13px;
    color:#6b7280;
}
</style>

<div class="container mt-4">

<div class="row mb-4">
<div class="col-lg-4 col-md-5">

<div class="profile-card p-4 shadow-sm">
<?= $msg ?>

<div class="d-flex align-items-center mb-3">
<?php if(!empty($user['foto'])): ?>
    <img src="<?= 'uploads/'.$user['foto'] ?>"
          class="rounded-circle me-3"
          width="72" height="72"
          style="object-fit:cover;">
<?php else: ?>
    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center"
          style="width:72px;height:72px;background:#f3f4f6;">
        <i class="bi bi-person fs-2 text-secondary"></i>
    </div>
<?php endif; ?>

<div>
    <h5 class="mb-1"><?= htmlspecialchars($user['nama']) ?></h5>
    <div class="text-muted-small"><?= htmlspecialchars($user['email']) ?></div>
    <?php if($user['no_telp']): ?>
        <div class="text-muted-small"><?= htmlspecialchars($user['no_telp']) ?></div>
    <?php endif; ?>
</div>
</div>

<button class="btn btn-outline-dark btn-sm w-100"
        data-bs-toggle="collapse"
        data-bs-target="#editProfileForm">
    Edit Profile
</button>

</div>
</div>
</div>

<div class="collapse mb-4" id="editProfileForm">
<div class="card border-0 shadow-sm p-4">
    <form id="editProfileFormTag" method="post" enctype="multipart/form-data">
        <input type="hidden" name="update_profile" value="1">

        <div class="mb-3">
            <label class="form-label">Foto Profil</label>
            <input type="file" name="foto" class="form-control" id="fotoInput">
        </div>

        <?php if(!empty($user['foto'])): ?>
            <div class="mb-3 text-start">
                <button type="button" class="btn btn-sm btn-outline-danger" id="deletePhotoBtn">
                    Hapus Foto Profil Saat Ini
                </button>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" id="namaInput"
                   value="<?= htmlspecialchars($user['nama']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" id="emailInput"
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" id="telpInput"
                   value="<?= htmlspecialchars($user['no_telp']) ?>">
        </div>

        <button class="btn btn-neutral w-100">Simpan Perubahan</button>
    </form>
</div>
</div>

<?php if(!empty($user['foto'])): ?>
    <form id="deletePhotoForm" method="post" style="display:none;">
        <input type="hidden" name="delete_photo" value="1">
    </form>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
<h5 class="section-title mb-0">Barang yang aku jual</h5>
<a href="jual.php" class="btn btn-sm btn-neutral rounded-pill px-4">Tambah Barang</a>
</div>

<div class="mb-5">
    <h6 class="fw-bold text-dark mb-3">
        Menunggu Persetujuan (Pending) 
    </h6>
    
    <?php if($pendingItemsResult->num_rows > 0): ?>
    <div class="row g-4">
        <?php while($item = $pendingItemsResult->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3"> 
            <div class="card h-100 pending-item product-card shadow-sm">
                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2" style="z-index: 10;">PENDING</span>
                
                <img src="<?= htmlspecialchars($item['foto'] ? 'uploads/'.$item['foto'] : 'assets/img/placeholder.png') ?>"
                     class="card-img-top"
                     style="height:150px;object-fit:cover;border-radius:14px 14px 0 0;">

                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-semibold mb-1 text-dark text-truncate" style="font-size: 0.95rem;">
                        <?= htmlspecialchars($item['nama_barang']) ?>
                    </h6>
                    <span class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                        Rp <?= number_format($item['harga']) ?>
                    </span>
                    <small class="text-muted text-truncate mt-auto">
                        Tanggal Posting: <?= date('d M Y', strtotime($item['created_at'])) ?>
                    </small>
                </div>

                <div class="card-footer bg-white border-0 p-3 pt-0 d-flex gap-2">
                    <a href="edit_barang.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
                        Edit
                    </a>
                    <a href="hapus_barang.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger w-100" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                        Hapus
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-dark border-0 text-center">
        Tidak ada barang yang sedang menunggu persetujuan Admin.
    </div>
    <?php endif; ?>
</div>

<hr class="mb-4">

<div class="mb-4">
    <h6 class="fw-bold text-dark mb-3">
        Barang Terposting (Approved) 
    </h6>
    
    <?php if($approvedItemsResult->num_rows > 0): ?>
    <div class="row g-4">
        <?php while($item = $approvedItemsResult->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 approved-item product-card shadow-sm">
                <img src="<?= htmlspecialchars($item['foto'] ? 'uploads/'.$item['foto'] : 'assets/img/placeholder.png') ?>"
                     class="card-img-top"
                     style="height:150px;object-fit:cover;border-radius:14px 14px 0 0;">

                <div class="card-body p-3 d-flex flex-column">
                    <h6 class="fw-semibold mb-1 text-dark text-truncate" style="font-size: 0.95rem;">
                        <?= htmlspecialchars($item['nama_barang']) ?>
                    </h6>
                    <span class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                        Rp <?= number_format($item['harga']) ?>
                    </span>
                    <small class="text-muted text-truncate mt-auto">
                        Tanggal Posting: <?= date('d M Y', strtotime($item['created_at'])) ?>
                    </small>
                </div>
                
                <div class="card-footer bg-white border-0 p-3 pt-0 d-flex gap-2">
                    <a href="edit_barang.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
                        Edit
                    </a>
                    <a href="hapus_barang.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger w-100" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                        Hapus
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info border-0 text-center">
        Anda belum memiliki barang yang ditampilkan di pasar.
    </div>
    <?php endif; ?>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.querySelector('[data-bs-target="#editProfileForm"]');
    const flashMsg = document.querySelector('.flash-msg');
    const deletePhotoBtn = document.getElementById('deletePhotoBtn');

    if(editBtn && flashMsg){
        editBtn.addEventListener('click', () => {
            if(flashMsg.parentElement.classList.contains('profile-card')) {
                 flashMsg.remove(); // hanya hapus notif profil
            }
        });
    }

    // NEW JAVASCRIPT: Trigger hidden delete form
    if(deletePhotoBtn){
        deletePhotoBtn.addEventListener('click', function(e){
            e.preventDefault();
            if(confirm('Apakah Anda yakin ingin menghapus foto profil?')){
                document.getElementById('deletePhotoForm').submit();
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>