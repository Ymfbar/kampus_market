<?php 
// BLOK LOGIKA HARUS DI ATAS SEMUA INCLUDE UNTUK MENCEGAH 'HEADERS ALREADY SENT'
session_start();
include 'includes/config.php';

// Ambil data user (harus sebelum POST jika kita butuh data lama)
$uid = $_SESSION['user']['id'] ?? 0;
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
    
    // Cek otentikasi
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }

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
include 'includes/auth.php'; 

// Ambil data user lagi (atau gunakan data dari atas)
$uid = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT nama, email, no_telp, foto FROM users WHERE id=? LIMIT 1");
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

// Flash message
$msg = '';
if (isset($_SESSION['flash_msg'])) {
    $msg = '<div class="alert alert-success flash-msg">'.$_SESSION['flash_msg'].'</div>';
    unset($_SESSION['flash_msg']);
}


// Ambil barang user
$itemStmt = $conn->prepare("SELECT * FROM items WHERE user_id=? ORDER BY created_at DESC");
$itemStmt->bind_param('i', $uid);
$itemStmt->execute();
$items = $itemStmt->get_result();
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
.item-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    transition:.2s;
}
.item-card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
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

<div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="section-title mb-0">Barang yang aku jual</h5>
<a href="jual.php" class="btn btn-sm btn-neutral">Tambah Barang</a>
</div>

<div class="row g-4">
<?php if($items->num_rows > 0): ?>
<?php while($item = $items->fetch_assoc()): ?>
<div class="col-md-6 col-lg-4">
<div class="item-card h-100 shadow-sm">
<img src="<?= $item['foto'] ? 'uploads/'.$item['foto'] : 'assets/img/placeholder.png' ?>"
     class="w-100"
     style="height:180px;object-fit:cover;border-radius:14px 14px 0 0;">

<div class="p-3">
<h6 class="mb-1"><?= htmlspecialchars($item['nama_barang']) ?></h6>
<div class="fw-semibold mb-1">Rp <?= number_format($item['harga']) ?></div>
<div class="text-muted-small mb-2"><?= $item['created_at'] ?></div>

<div class="d-flex gap-2">
<a href="edit_barang.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-dark w-100">Edit</a>
<a href="hapus_barang.php?id=<?= $item['id'] ?>" 
   class="btn btn-sm btn-outline-danger w-100"
   onclick="return confirm('Yakin hapus?')">Hapus</a>
</div>
</div>
</div>
</div>
<?php endwhile; ?>
<?php else: ?>
<div class="alert alert-light border text-center">
Belum ada barang yang kamu jual
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
            flashMsg.remove(); // hanya hapus notif profil
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

    // Cek perubahan sebelum submit
    const form = document.getElementById('editProfileFormTag');
    const originalData = {
        nama: document.getElementById('namaInput').value,
        email: document.getElementById('emailInput').value,
        no_telp: document.getElementById('telpInput').value,
        foto: document.getElementById('fotoInput').value
    };

    form.addEventListener('submit', function(e){
        const currentData = {
            nama: document.getElementById('namaInput').value,
            email: document.getElementById('emailInput').value,
            no_telp: document.getElementById('telpInput').value,
            foto: document.getElementById('fotoInput').value
        };

        if(currentData.nama === originalData.nama &&
           currentData.email === originalData.email &&
           currentData.no_telp === originalData.no_telp &&
           currentData.foto === ''){
            e.preventDefault();
            alert('Tidak ada perubahan yang dilakukan');
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>