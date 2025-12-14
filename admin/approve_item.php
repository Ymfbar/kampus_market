// ymfbar/kampus_market/kampus_market-0bdaf0d5a808d5a69b67d6f643b0aaae9a1157fd/admin/approve_item.php

<?php
session_start();
include '../includes/config.php';

// Pastikan hanya admin yang bisa mengakses
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){ 
    header("Location: ../index.php"); 
    exit; 
}

if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $item_id = (int)$_GET['id'];
    
    // Update status item menjadi 'approved'
    $stmt = $conn->prepare("UPDATE items SET status = 'approved' WHERE id = ?");
    $stmt->bind_param('i', $item_id);

    if($stmt->execute()){
        $_SESSION['flash_msg_admin'] = "Item ID $item_id berhasil di-approve.";
    } else {
        $_SESSION['flash_msg_admin'] = "Gagal meng-approve Item ID $item_id.";
    }
} else {
    $_SESSION['flash_msg_admin'] = "ID item tidak valid.";
}

// Redirect kembali ke halaman manage items
header("Location: manage_items.php");
exit;
?>