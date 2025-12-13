<?php include 'includes/header.php'; ?>

<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("
    SELECT i.*, u.nama AS seller, u.email AS seller_email 
    FROM items i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.id=?
");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows === 0){
    echo "<div class='alert alert-warning'>Barang tidak ditemukan.</div>";
    include 'includes/footer.php'; 
    exit;
}
$item = $res->fetch_assoc();
?>

<div class="container my-4">

    <div class="row g-4">

        <!-- FOTO BARANG -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <img 
                    src="<?= htmlspecialchars($item['foto'] ? 'uploads/'.$item['foto'] : 'assets/img/placeholder.png') ?>" 
                    class="img-fluid rounded"
                    style="height:420px;object-fit:cover;"
                >
            </div>
        </div>

        <!-- DETAIL BARANG -->
        <div class="col-md-6">

            <h3 class="fw-bold mb-1"><?= htmlspecialchars($item['nama_barang']) ?></h3>
            <div class="text-danger fs-4 fw-bold mb-2">
                Rp <?= number_format($item['harga']) ?>
            </div>

            <p class="text-muted mb-3" style="white-space:pre-line;">
                <?= htmlspecialchars($item['deskripsi']) ?>
            </p>

            <!-- SELLER CARD -->
            <div class="card p-3 mb-3 border-0 shadow-sm bg-light">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center"
                             style="width:48px;height:48px;">
                            <?= strtoupper(substr($item['seller'],0,1)) ?>
                        </div>
                    </div>
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($item['seller']) ?></div>
                        <small class="text-muted">Penjual terpercaya</small>
                    </div>
                </div>
            </div>

            <?php if(isset($_SESSION['user'])): ?>

                <?php if($_SESSION['user']['id'] != $item['user_id']): ?>

                <!-- CHAT SELLER -->
                <div class="card p-3 border-0 shadow-sm">
                    <h6 class="fw-bold mb-2">Tanya ke Penjual</h6>
                    <form method="POST" action="send_message.php">
                        <input type="hidden" name="receiver_id" value="<?= $item['user_id'] ?>">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <textarea 
                            class="form-control mb-2"
                            name="pesan"
                            rows="3"
                            placeholder="Halo, apakah barang ini masih tersedia?"
                            required
                        ></textarea>
                        <button class="btn btn-dark w-100">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                <?php else: ?>
                    <div class="alert alert-info mt-3">
                        ℹ️ Ini adalah barang yang kamu jual
                    </div>
                <?php endif; ?>

            <?php else: ?>

                <!-- JIKA BELUM LOGIN -->
                <div class="card p-3 border-0 shadow-sm">
                    <h6 class="fw-bold mb-2">Tanya ke Penjual</h6>
                    <form method="POST" action="send_message.php">
                        <input type="hidden" name="receiver_id" value="<?= $item['user_id'] ?>">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <textarea 
                            class="form-control mb-2"
                            name="pesan"
                            rows="3"
                            placeholder="Halo, apakah barang ini masih tersedia?"
                        
                        ></textarea>
                        <button class="btn btn-dark w-100">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<style>
.btn-lg {
    border-radius: 14px;
}
</style>

<?php include 'includes/footer.php'; ?>
