<?php 
// BLOK LOGIKA INI HARUS DI ATAS SEMUA INCLUDE YANG MENGHASILKAN OUTPUT!
session_start();
include 'includes/config.php';
include 'includes/auth.php'; // Periksa otentikasi lebih awal

$msg = '';

// Mengambil pesan flash dari session jika ada
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nama = trim($_POST['nama_barang']);
    $harga = (int) str_replace('.', '', $_POST['harga']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = (int) $_POST['kategori'];

    $upload_error = false;
    
    // --- BAGIAN BARU: Penanganan Bukti Bayar Tax Admin (Opsional) ---
    $bukti_bayar_tax_name = NULL;
    $has_tax_upload = false;
    
    if(isset($_FILES['bukti_bayar_tax']) && $_FILES['bukti_bayar_tax']['error'] === UPLOAD_ERR_OK){
        $has_tax_upload = true;
        $ext_tax = pathinfo($_FILES['bukti_bayar_tax']['name'], PATHINFO_EXTENSION);
        // Izinkan format gambar dan PDF untuk bukti pembayaran
        $allow_tax = ['jpg','jpeg','png','webp','pdf']; 
        
        if(!in_array(strtolower($ext_tax), $allow_tax)){
            $_SESSION['flash_msg'] = "Format bukti bayar tidak valid (hanya JPG, PNG, WEBP, PDF)";
            $upload_error = true;
        } else {
            $bukti_bayar_tax_name = uniqid('tax_').'.'.$ext_tax;
        }
    }
    // --- AKHIR BAGIAN BARU ---

    if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK){
        $_SESSION['flash_msg'] = "Foto wajib di-upload";
        $upload_error = true;
    } 

    if (!$upload_error) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allow = ['jpg','jpeg','png','webp'];
        if(!in_array(strtolower($ext), $allow)){
            $_SESSION['flash_msg'] = "Format foto tidak valid";
            $upload_error = true;
        } 
    }

    if (!$upload_error) {
        $foto_name = uniqid('itm_').'.'.$ext;
        
        if($nama){
            // --- MODIFIKASI INSERT QUERY: Tambah bukti_bayar_tax di sini ---
            $stmt = $conn->prepare(
                "INSERT INTO items (user_id, kategori_id, nama_barang, deskripsi, harga, foto, bukti_bayar_tax)
                 VALUES (?,?,?,?,?,?,?)"
            );
            $uid = $_SESSION['user']['id'];
            // Tambah 's' untuk parameter bukti_bayar_tax
            $stmt->bind_param('iississ', $uid, $kategori, $nama, $deskripsi, $harga, $foto_name, $bukti_bayar_tax_name);

            if($stmt->execute()){
                // Pindahkan file foto utama
                move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/'.$foto_name);
                
                // --- Pindahkan file bukti bayar jika ada ---
                if($has_tax_upload){
                    move_uploaded_file($_FILES['bukti_bayar_tax']['tmp_name'], 'uploads/'.$bukti_bayar_tax_name);
                }
                
                // PRG: Lakukan REDIRECT setelah POST berhasil
                $_SESSION['flash_msg'] = "Barang berhasil diposting";
                header("Location: jual.php");
                exit;
            } else {
                $_SESSION['flash_msg'] = "Gagal posting barang";
            }
        } else {
            $_SESSION['flash_msg'] = "Nama barang wajib diisi";
        }
        
        // Redirect jika ada error POST tapi bukan error header
        if(isset($_SESSION['flash_msg'])){
            header("Location: jual.php");
            exit;
        }
    }
}
?>

<?php 
// Panggil header setelah semua logika redirect selesai
include 'includes/header.php'; 

// ambil kategori (Setelah include header untuk mendapatkan koneksi yang bersih)
$catResult = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");
?>

<style>
.sell-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    overflow:hidden;
}
.upload-area{
    /* PERUBAHAN DI SINI: Warna putih */
    background: #ffffff; /* Putih */
    border-right:1px solid #e5e7eb; /* Batas abu-abu standar */
}
.upload-placeholder{
    height:260px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:14px;
}
.btn-neutral{
    background:#111827;
    color:#fff;
    border-radius:999px;
    font-weight:600;
}
.btn-neutral:hover{
    background:#000;
}
.form-control,
.form-select{
    border-radius:12px;
}
</style>

<div class="text-center">
        <img src="uploads/logoku.png" 
             alt="Logo"
             style="width:1200px; height:120px; object-fit:contain;">
    </div>

<h4 class="fw-semibold mb-3">🛒 Jual Barang</h4>

<?php if($msg): ?>
<div class="alert alert-light border"><?= $msg ?></div>
<?php endif; ?>

<div class="sell-card shadow-sm mb-4">
<form method="POST" enctype="multipart/form-data">

<div class="row g-0">

<div class="col-md-5 upload-area p-4 d-flex flex-column justify-content-center">

<label for="foto" style="cursor:pointer;">
    <div class="upload-placeholder mb-3">
        <img id="preview"
             src="assets/img/upload_placeholder.png"
             class="img-fluid"
             style="max-height:200px;object-fit:contain;">
    </div>

    <div class="text-center">
        <div class="btn btn-outline-dark rounded-pill px-4">
            Upload Foto Barang (Wajib)
        </div>
        <p class="text-muted small mt-2">
            JPG, PNG, WEBP • Maks 2MB
        </p>
    </div>

    <input type="file" name="foto" id="foto" class="d-none" required>
</label>

</div>

<div class="col-md-7 p-4">

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="kategori" class="form-select" required>
        <option value="">Pilih kategori</option>
        <?php while($cat = $catResult->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['nama_kategori']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Nama Barang</label>
    <input name="nama_barang" class="form-control" required>
</div>

<div class="mb-3">
    <label class="form-label">Harga</label>
    <input name="harga" id="harga" type="text" class="form-control" required>
</div>

<div class="mb-4">
    <label class="form-label">Deskripsi</label>
    <textarea name="deskripsi" class="form-control" rows="4"></textarea>
</div>

<div class="mb-4">
    <label class="form-label">Admin Tax (Rp.2000)</label>
    <input type="file" name="bukti_bayar_tax" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
</div>
<button class="btn btn-neutral w-100 py-2">
    Posting Barang
</button>

</div>
</div>
</form>
</div>

<script>
document.getElementById('harga').addEventListener('input', function () {
    let value = this.value.replace(/\D/g, "");
    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
});

document.getElementById('foto').addEventListener('change', function(e){
    const file = e.target.files[0];
    if(file){
        document.getElementById('preview').src = URL.createObjectURL(file);
    }
});
</script>

<?php include 'includes/footer.php'; ?>