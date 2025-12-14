<?php
include '../includes/header.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

// LOGIKA STATISTIK
$stats = [];
$stats['users'] = $conn->query("SELECT COUNT(id) AS count FROM users")->fetch_assoc()['count'];
$stats['items'] = $conn->query("SELECT COUNT(id) AS count FROM items")->fetch_assoc()['count'];
$stats['messages'] = $conn->query("SELECT COUNT(id) AS count FROM messages")->fetch_assoc()['count'];

// LOGIKA BARANG TERBARU (Sama seperti di index.php)
$sql = "SELECT i.*, u.nama AS seller 
        FROM items i 
        JOIN users u ON i.user_id = u.id 
        ORDER BY i.created_at DESC";
$res = $conn->query($sql);
?>

<div class="container my-5">

    <div class="text-center mb-5">
        <img src="../uploads/logoku.png" alt="Preppy Finds"
             class="hero-logo mb-1">

        <h1 class="fw-bold display-6 mb-2 text-dark">
            <span class="fw-semibold text-dark">Welcome, Admin <?= htmlspecialchars($_SESSION['user']['nama']) ?>!</span>
        </h1>

        <p class="text-muted mb-4">
            You're doing an amazing job keeping everything running smoothly!
        </p>

        <div class="row justify-content-center g-4 mb-5">
            
            <div class="col-md-3">
                <div class="stat-card-minimal">
                    <h5 class="fw-bold text-dark mb-0"><?= $stats['users'] ?></h5>
                    <p class="small text-muted mb-0">Total User</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card-minimal">
                    <h5 class="fw-bold text-dark mb-0"><?= $stats['items'] ?></h5>
                    <p class="small text-muted mb-0">Total Postingan</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card-minimal">
                    <h5 class="fw-bold text-dark mb-0"><?= $stats['messages'] ?></h5>
                    <p class="small text-muted mb-0">Total Interaksi Chat</p>
                </div>
            </div>
        </div>
        
        <hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark">Barang Terbaru</h5>
        <a href="../search.php" class="btn btn-sm btn-outline-dark rounded-pill">
            Lihat Semua
        </a>
    </div>

    <div class="row g-4">
    <?php while($row = $res->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm product-card">
                <img src="<?= htmlspecialchars($row['foto'] ? '../uploads/'.$row['foto'] : '../assets/img/placeholder.png') ?>"
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

                    <a href="../detail.php?id=<?= $row['id'] ?>"
                       class="btn btn-sm btn-outline-dark rounded-pill mt-auto">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
    
    <style>
    /* Custom Styling for Minimalist Stats Card */
    .stat-card-minimal {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    }
    .stat-card-minimal:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
    }
    .stat-card-minimal h5 {
        font-size: 2rem; /* Ukuran angka lebih besar */
    }

    /* hero logo */
    .hero-logo{
        width: clamp(200px, 30vw, 400px); /* Dibuat lebih responsif dan tidak terlalu lebar */
        height:100px;
        object-fit:contain;
    }

    /* product card */
    .product-card{
        transition: all .25s ease;
        border-radius: 12px;
    }
    .product-card:hover{
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(0,0,0,.1);
    }
    </style>

</div>

<?php include '../includes/footer.php'; ?>