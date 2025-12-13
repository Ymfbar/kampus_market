<?php
include 'includes/config.php';
if(!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT foto FROM items WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii',$id,$uid);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0){
    header("Location: profile.php"); exit;
}
$row = $res->fetch_assoc();
if($row['foto'] && file_exists('uploads/'.$row['foto'])) @unlink('uploads/'.$row['foto']);

$stmt2 = $conn->prepare("DELETE FROM items WHERE id=? AND user_id=?");
$stmt2->bind_param('ii',$id,$uid);
$stmt2->execute();

header("Location: profile.php");
exit;
