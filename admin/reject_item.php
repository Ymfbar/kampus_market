<?php
session_start();
require_once '../includes/config.php'; // Perbaikan: Mengganti 'include' menjadi 'require_once'

// Pastikan hanya admin yang bisa mengakses
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ 
    header("Location: ../index.php"); 
    exit; 
}

// Cek apakah koneksi database ada (walaupun require_once sudah cukup, ini untuk pencegahan)
if (!isset($conn) || $conn === null) {
    $_SESSION['flash_msg_admin'] = "ERROR: Koneksi database gagal diinisialisasi.";
    header("Location: manage_items.php");
    exit;
}

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $item_id = (int)$_GET['id'];
    
    // Update status item menjadi 'rejected'
    $stmt_update = $conn->prepare("UPDATE items SET status = 'rejected' WHERE id = ?");
    $stmt_update->bind_param('i', $item_id);

    if($stmt_update->execute()){
        // PENTING: Notifikasi sukses untuk admin Dihilangkan (sesuai permintaan Anda)
        // $_SESSION['flash_msg_admin'] = "Item ID $item_id berhasil di-TOLAK.";
        
        // --- LOGIKA NOTIFIKASI USER (Pemilik Barang) ---
        
        // Dapatkan user_id dan nama_barang dari item yang ditolak (Menggunakan prepared statement)
        $stmt_select = $conn->prepare("SELECT user_id, nama_barang FROM items WHERE id = ?");
        $stmt_select->bind_param('i', $item_id);
        $stmt_select->execute();
        $res_user = $stmt_select->get_result();
        
        if($res_user->num_rows > 0){
            $item_data = $res_user->fetch_assoc();
            
            // Set session notifikasi untuk user pemilik barang, yang akan ditampilkan di header.php
            $_SESSION['rejected_item_alert'] = [
                'user_id' => $item_data['user_id'],
                'item_id' => $item_id,
                'nama_barang' => $item_data['nama_barang']
            ];
        }
        $stmt_select->close(); // Tutup statement SELECT
    } else {
        $_SESSION['flash_msg_admin'] = "Gagal menolak Item ID $item_id. " . $conn->error;
    }
    $stmt_update->close(); // Tutup statement UPDATE
} else {
    $_SESSION['flash_msg_admin'] = "ID item tidak valid.";
}

// Redirect kembali ke halaman manage items
header("Location: manage_items.php");
exit;
?>