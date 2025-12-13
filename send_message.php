<?php
session_start();        // WAJIB!
include 'includes/config.php';

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$sender = $_SESSION['user']['id'];
$receiver = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : null;
$pesan = trim($_POST['pesan']);

if($receiver && $pesan){
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, item_id, pesan) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiis', $sender, $receiver, $item_id, $pesan);
    $stmt->execute();
}

header("Location: inbox.php");
exit;
