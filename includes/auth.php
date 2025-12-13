<?php
// gunakan include 'includes/auth.php' pada halaman yang butuh login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>
