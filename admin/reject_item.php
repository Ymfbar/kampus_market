<?php
// ymfbar/kampus_market/kampus_market-cd0173e5a51ce6d96bdd00e90756a46dc515d285/admin/reject_item.php
session_start();
include '../includes/config.php';

// Pastikan hanya admin yang bisa mengakses
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ 
    header("Location: ../index.php"); 
    exit; 
}

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $item_id = (int)$_GET['id'];
    
    // Update status item menjadi 'rejected'
    $stmt = $conn->prepare("UPDATE items SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param('i', $item_id);

    if($stmt->execute()){
        $_SESSION['flash_msg_admin'] = "Item ID $item_id berhasil di-TOLAK.";
        
        // --- LOGIKA NOTIFIKASI USER ---
        // Simpan ID item yang ditolak di session untuk dicek oleh user saat memuat halaman
        // Kita perlu mendapatkan user_id dari item yang ditolak untuk notifikasi
        $res_user = $conn->query("SELECT user_id, nama_barang FROM items WHERE id = $item_id");
        if($res_user->num_rows > 0){
            $item_data = $res_user->fetch_assoc();
            
            // Set session notifikasi untuk user, yang akan ditampilkan di header.php
            $_SESSION['rejected_item_alert'] = [
                'user_id' => $item_data['user_id'],
                'item_id' => $item_id,
                'nama_barang' => $item_data['nama_barang']
            ];
        }
    } else {
        $_SESSION['flash_msg_admin'] = "Gagal menolak Item ID $item_id. " . $conn->error;
    }
} else {
    $_SESSION['flash_msg_admin'] = "ID item tidak valid.";
}

// Redirect kembali ke halaman manage items
header("Location: manage_items.php");
exit;
?>