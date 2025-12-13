<?php
include 'includes/config.php';

$notif = 0;

if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];

    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM messages 
        WHERE receiver_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($notif);
    $stmt->fetch();
    $stmt->close();
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
/* ================= SIDEBAR ================= */
#sidebar{
    width:70px;
    height:100vh;
    background:#000;
    position:fixed;
    top:0;
    left:0;
    padding-top:20px;
    overflow:hidden;
    transition:.3s;
    z-index:1000;
}

#sidebar:hover{
    width:230px;
}

/* ================= MENU ITEM ================= */
#sidebar .menu-item{
    display:flex;
    align-items:center;
    padding:12px 15px;
    color:#fff;
    text-decoration:none;
    white-space:nowrap;
    transition:.3s;
}

#sidebar .menu-item:hover{
    background:rgba(255,255,255,.1);
}

/* ================= ICON FIX (INI KUNCI) ================= */
#sidebar .icon{
    width:30px;
    min-width:30px;   /* ⬅️ WAJIB */
    height:30px;
    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
    font-size:20px;
}

/* IMAGE ICON (LOGO) */
#sidebar .icon img{
    width:100%;
    height:100%;
    object-fit:contain;
    display:block;
}

/* ================= TEXT ================= */
#sidebar .text{
    opacity:0;
    margin-left:12px;
    transition:.3s;
}

#sidebar:hover .text{
    opacity:1;
}

/* ================= BRAND ================= */
#sidebar .brand{
    font-weight:700;
    margin-bottom:25px;
}

/* ================= NOTIF BADGE ================= */
.badge-notif{
    background:red;
    color:#fff;
    font-size:10px;
    padding:2px 6px;
    border-radius:50px;
    position:absolute;
    top:-4px;
    right:-6px;
}

/* ================= MAIN CONTENT SHIFT ================= */
#main-content{
    margin-left:70px;
    transition:.3s;
}

#sidebar:hover ~ #main-content{
    margin-left:230px;
}
</style>

<div id="sidebar">

    <!-- BRAND -->
    <a href="index.php" class="menu-item brand">
        <span class="icon">
            <img src="uploads/logo.png" alt="Preppy Finds">
        </span>
        <span class="text">Preppy Finds</span>
    </a>

    <a href="index.php" class="menu-item">
        <span class="icon"><i class="bi bi-house"></i></span>
        <span class="text">Home</span>
    </a>

    <a href="search.php" class="menu-item">
        <span class="icon"><i class="bi bi-search"></i></span>
        <span class="text">Search</span>
    </a>

<?php if(isset($_SESSION['user'])): ?>

    <a href="jual.php" class="menu-item">
        <span class="icon"><i class="bi bi-plus-circle"></i></span>
        <span class="text">Jual</span>
    </a>

    <!-- INBOX -->
    <a href="inbox.php" class="menu-item">
        <span class="icon">
            <i class="bi bi-envelope"></i>
            <?php if($notif > 0): ?>
                <span class="badge-notif"><?= $notif ?></span>
            <?php endif; ?>
        </span>
        <span class="text">Inbox</span>
    </a>

    <a href="profile.php" class="menu-item">
        <span class="icon"><i class="bi bi-person"></i></span>
        <span class="text">Profile</span>
    </a>

    <?php if($_SESSION['user']['role']==='admin'): ?>
        <a href="admin/dashboard.php" class="menu-item">
            <span class="icon"><i class="bi bi-gear"></i></span>
            <span class="text">Admin</span>
        </a>
    <?php endif; ?>

    <a href="logout.php" class="menu-item"
       onclick="return confirm('Yakin ingin logout? :(');">
        <span class="icon"><i class="bi bi-door-closed"></i></span>
        <span class="text">Logout</span>
    </a>

<?php else: ?>

    <a href="login.php" class="menu-item">
        <span class="icon"><i class="bi bi-key"></i></span>
        <span class="text">Login</span>
    </a>

    <a href="register.php" class="menu-item">
        <span class="icon"><i class="bi bi-pencil-square"></i></span>
        <span class="text">Register</span>
    </a>

<?php endif; ?>

</div>

<div id="main-content">
