<?php 
include 'includes/header.php'; 
include 'includes/auth.php'; 

// ambil kategori
$catResult = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");

$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nama = trim($_POST['nama_barang']);
    $harga = (int) str_replace('.', '', $_POST['harga']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = (int) $_POST['kategori'];

    if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK){
        $msg = "Foto wajib di-upload";
    } else {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allow = ['jpg','jpeg','png','webp'];
        if(!in_array(strtolower($ext), $allow)){
            $msg = "Format foto tidak valid";
        } else {
            $foto_name = uniqid('itm_').'.'.$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/'.$foto_name);

            if($nama){
                $stmt = $conn->prepare(
                    "INSERT INTO items (user_id, kategori_id, nama_barang, deskripsi, harga, foto)
                     VALUES (?,?,?,?,?,?)"
                );
                $uid = $_SESSION['user']['id'];
                $stmt->bind_param('iissis', $uid, $kategori, $nama, $deskripsi, $harga, $foto_name);

                if($stmt->execute()){
                    $msg = "Barang berhasil diposting";
                } else {
                    $msg = "Gagal posting barang";
                }
            } else {
                $msg = "Nama barang wajib diisi";
            }
        }
    }
}
?>

<style>
.sell-card{
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:18px;
    overflow:hidden;
}
.upload-area{
    background:#f9fafb;
    border-right:1px solid #e5e7eb;
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

<div class="mb-2 text-center">
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

<!-- LEFT : UPLOAD -->
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
            Upload Foto
        </div>
        <p class="text-muted small mt-2">
            JPG, PNG, WEBP • Maks 2MB
        </p>
    </div>

    <input type="file" name="foto" id="foto" class="d-none" required>
</label>

</div>

<!-- RIGHT : FORM -->
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
