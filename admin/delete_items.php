<?php
include '../includes/config.php';
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='admin'){ header("Location: ../index.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT foto FROM items WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows){
    $r = $res->fetch_assoc();
    if($r['foto'] && file_exists('../uploads/'.$r['foto'])) @unlink('../uploads/'.$r['foto']);
}
$stmt2 = $conn->prepare("DELETE FROM items WHERE id=?");
$stmt2->bind_param('i',$id);
$stmt2->execute();

header("Location: manage_items.php");
exit;
