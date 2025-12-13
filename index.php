<?php include 'includes/header.php'; ?>

<!-- HERO SECTION -->
<div class="container my-5">

    <div class="text-center mb-5">
        <img src="uploads/logo.png" alt="Preppy Finds"
             class="hero-logo mb-1">

        <h1 class="fw-bold display-6 mb-2 text-dark">
            <?php if(isset($_SESSION['user'])): ?>
                Hai, <?= htmlspecialchars($_SESSION['user']['nama']) ?>  
                <br><span class="fw-semibold text-secondary">Welcome to Preppy Finds</span>
            <?php else: ?>
                <span class="fw-semibold text-dark">Preppy Finds</span>
            <?php endif; ?>
        </h1>

        <p class="text-muted mb-4">
            Platform jual beli barang bekas kebutuhan kampus
        </p>

        <!-- VALUE IMAGE ICON -->
        <div class="row justify-content-center value-icons">

            <div class="col-4 col-md-3 col-lg-2">
                <div class="text-center">
                    <img src="uploads/1.png" class="value-img mb-2">
                    <p class="small fw-semibold mb-0 text-dark">Harga Terjangkau</p>
                </div>
            </div>

            <div class="col-4 col-md-3 col-lg-2">
                <div class="text-center">
                    <img src="uploads/2.png" class="value-img mb-2">
                    <p class="small fw-semibold mb-0 text-dark">Pengiriman Cepat</p>
                </div>
            </div>

            <div class="col-4 col-md-3 col-lg-2">
                <div class="text-center">
                    <img src="uploads/3.png" class="value-img mb-2">
                    <p class="small fw-semibold mb-0 text-dark">Komunitas Mahasiswa</p>
                </div>
            </div>

        </div>
    </div>

    <!-- SECTION TITLE -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark">Barang Terbaru</h5>
        <a href="search.php" class="btn btn-sm btn-outline-dark rounded-pill">
            Lihat Semua
        </a>
    </div>

    <!-- PRODUCT GRID -->
    <div class="row g-4">
    <?php
    $sql = "SELECT i.*, u.nama AS seller 
            FROM items i 
            JOIN users u ON i.user_id = u.id 
            ORDER BY i.created_at DESC";
    $res = $conn->query($sql);
    while($row = $res->fetch_assoc()):
    ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm product-card">
                <img src="<?= htmlspecialchars($row['foto'] ? 'uploads/'.$row['foto'] : 'assets/img/placeholder.png') ?>"
                     class="card-img-top"
                     style="height:180px;object-fit:cover;">

                <div class="card-body d-flex flex-column">
                    <h6 class="fw-semibold mb-1 text-dark">
                        <?= htmlspecialchars($row['nama_barang']) ?>
                    </h6>

                    <span class="fw-bold mb-1 text-dark">
                        Rp <?= number_format($row['harga']) ?>
                    </span>

                    <small class="text-muted mb-3">
                        <?= htmlspecialchars($row['seller']) ?>
                    </small>

                    <a href="detail.php?id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-outline-dark rounded-pill mt-auto">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

</div>

<!-- STYLE -->
<style>
/* hero logo */
.hero-logo{
    width:100px;
    height:100px;
    object-fit:contain;
}

/* product card */
.product-card{
    transition: all .25s ease;
}
.product-card:hover{
    transform: translateY(-4px);
    box-shadow: 0 18px 35px rgba(0,0,0,.1);
}

/* value icon image */
.value-img{
    width:clamp(70px, 8vw, 100px);
    height:clamp(70px, 8vw, 100px);
    object-fit:contain;
    opacity:.9;
    transition:.25s ease;
}
.value-img:hover{
    opacity:1;
    transform: scale(1.08);
}
</style>

<?php include 'includes/footer.php'; ?>
