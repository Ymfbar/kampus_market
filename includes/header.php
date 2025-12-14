<?php
// AMAN: session cuma jalan sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/config.php';

// --- LOGIKA NOTIFIKASI BARANG DITOLAK ---
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user') {
    $current_user_id = $_SESSION['user']['id'];
    
    // Cek apakah ada notifikasi penolakan yang menunggu untuk user ini
    if (isset($_SESSION['rejected_item_alert']) && $_SESSION['rejected_item_alert']['user_id'] == $current_user_id) {
        // Notifikasi akan ditampilkan di bagian HTML di bawah
        // Item yang ditolak sudah ada di session
    } else {
        // Cek DB, pastikan notifikasi sudah dibaca/dihilangkan. 
        // Ini adalah fallback untuk kasus notifikasi session terhapus/gagal.
        // Cek apakah ada item rejected yang belum pernah diberi notifikasi (atau hanya cek status rejected)
        
        // Cek sederhana: Cek item rejected milik user, batasi 1 untuk notifikasi pop-up.
        // Jika Anda ingin notifikasi hanya muncul *sekali*, gunakan logika di `reject_item.php`
        // dan hanya tampilkan jika session `rejected_item_alert` ada.
        
        // Logika saat ini hanya mengandalkan session yang di-set di reject_item.php,
        // yang akan terhapus setelah ditampilkan di bagian HTML di bawah.
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Preppy Finds</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if (isset($_SESSION['rejected_item_alert'])): ?>
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
    <p style="margin-bottom: 20px;">Barang Anda: "<strong><?= htmlspecialchars($_SESSION['rejected_item_alert']['nama_barang']) ?></strong>" telah **DITOLAK** oleh Admin.</p>
    
    <a href="../profile.php" 
       class="btn btn-sm" 
       style="background-color: #b45309; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;"
       onclick="document.getElementById('rejectedNotification').style.display='none';">
        Pratinjau (Ke Profile)
    </a>
</div>
<?php 
    // Hapus session notifikasi agar pop-up tidak muncul berulang kali di halaman lain
    unset($_SESSION['rejected_item_alert']); 
?>
<?php endif; ?>

<?php include_once __DIR__ . '/navbar.php'; ?>

<div class="container mt-4">
