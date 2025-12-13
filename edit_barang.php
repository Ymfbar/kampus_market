<?php 
include 'includes/header.php'; 
include 'includes/auth.php'; 

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = $_SESSION['user']['id'];

// Ambil data item
$stmt = $conn->prepare("SELECT * FROM items WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii',$id,$uid);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    echo "<div class='alert alert-warning'>Barang tidak ditemukan atau bukan milikmu.</div>";
    include 'includes/footer.php'; 
    exit;
}
$item = $res->fetch_assoc();

// Ambil semua kategori
$catResult = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");

$msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nama = trim($_POST['nama_barang']);
    $harga = (int) str_replace('.', '', $_POST['harga']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori = (int) $_POST['kategori'];

    // Foto
    $foto_name = $item['foto'];
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $allow = ['jpg','jpeg','png','webp'];
        if(in_array(strtolower($ext), $allow)){
            $foto_name = uniqid('itm_').'.'.$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/'.$foto_name);
        }
    }

    // Update item
    $stmt2 = $conn->prepare("UPDATE items SET nama_barang=?, deskripsi=?, harga=?, foto=?, kategori_id=? WHERE id=? AND user_id=?");
    $stmt2->bind_param('ssisiii',$nama,$deskripsi,$harga,$foto_name,$kategori,$id,$uid);

    if($stmt2->execute()){
        $msg = "Update berhasil.";
        // refresh data
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
    } else {
        $msg = "Gagal update.";
    }
}
?>

<h3>Edit Barang</h3>
<?php if($msg): ?><div class="alert alert-info"><?= $msg ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">

  <!-- KATEGORI -->
  <div class="mb-2">
    <select name="kategori" class="form-select" required>
        <option value="">-- Pilih Kategori --</option>
        <?php while($cat = $catResult->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= ($item['kategori_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nama_kategori']) ?>
            </option>
        <?php endwhile; ?>
    </select>
  </div>

  <!-- NAMA -->
  <div class="mb-2">
      <input name="nama_barang" class="form-control" value="<?= htmlspecialchars($item['nama_barang']) ?>" required>
  </div>

  <!-- HARGA -->
  <div class="mb-2">
      <input name="harga" id="harga" type="text" class="form-control" value="<?= htmlspecialchars($item['harga']) ?>" required>
  </div>

  <!-- DESKRIPSI -->
  <div class="mb-2">
      <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($item['deskripsi']) ?></textarea>
  </div>

  <!-- FOTO -->
  <div class="mb-2">
      <small>Foto sekarang:</small><br>
      <img src="<?= htmlspecialchars($item['foto'] ? 'uploads/'.$item['foto'] : 'assets/img/placeholder.png') ?>" style="width:150px;height:100px;object-fit:cover;">
  </div>
  <div class="mb-2"><input type="file" name="foto" class="form-control"></div>

  <!-- BUTTON -->
  <button class="btn btn-success">Update</button>
</form>

<script>
document.getElementById('harga').addEventListener('input', function () {
    let value = this.value.replace(/\D/g, ""); // hanya angka
    let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    this.value = formatted;
});
</script>

<?php include 'includes/footer.php'; ?>
