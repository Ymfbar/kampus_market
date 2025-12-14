<?php
// AMAN: session cuma jalan sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config.php';

// Inisialisasi flag untuk mengontrol tampilan notifikasi
$show_rejected_alert = false;

// --- LOGIKA NOTIFIKASI BARANG DITOLAK ---
// Hanya tampilkan notifikasi jika:
// 1. User sedang login dan rolenya 'user'
// 2. Ada notifikasi reject di session
// 3. user_id di notifikasi reject SAMA dengan user_id yang sedang login
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user' && 
    isset($_SESSION['rejected_item_alert']) && 
    isset($_SESSION['rejected_item_alert']['user_id']) && 
    $_SESSION['rejected_item_alert']['user_id'] == $_SESSION['user']['id']) {
    
    $show_rejected_alert = true;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Preppy Finds</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if ($show_rejected_alert): // Notifikasi hanya tampil jika $show_rejected_alert true ?>
<div id="rejectedNotification" style="
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fef3c7; /* Kuning muda */
    border: 1px solid #b45309;
    padding: 25px 35px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    z-index: 9999; /* Pastikan di atas elemen lain */
    max-width: 350px;
    text-align: center;
">
    <h4 style="color: #b45309; margin-bottom: 10px;">⚠️ PEMBERITAHUAN PENTING</h4>
    <p style="margin-bottom: 20px;">Barang Anda: "<strong><?= htmlspecialchars($_SESSION['rejected_item_alert']['nama_barang']) ?></strong>" telah **DITOLAK** oleh Admin. Cek detail di halaman Profile Anda.</p>
    
    <a href="profile.php" 
        class="btn btn-sm" 
        style="background-color: #b45309; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;"
        onclick="document.getElementById('rejectedNotification').style.display='none';">
        Lihat Profile
    </a>
</div>
<?php 
    // Hapus session notifikasi agar pop-up tidak muncul berulang kali setelah ditampilkan
    unset($_SESSION['rejected_item_alert']); 
?>
<?php endif; ?>

<?php include_once __DIR__ . '/navbar.php'; ?>

<div class="container mt-4">