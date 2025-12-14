<?php
// PERBAIKAN 1: Menggunakan __DIR__ untuk fix 'Failed to open stream' error
include __DIR__ . '/config.php';

$notif = 0;

// PERBAIKAN PENTING: Pindahkan definisi $prefix di awal agar selalu tersedia
$is_admin_page = (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false);
$prefix = $is_admin_page ? '../' : '';


if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $nama = $_SESSION['user']['nama']; // Ambil nama user dari session
    
    // Logic untuk notifikasi
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

    // Logic untuk mengambil Foto Profil (Menggunakan kolom 'foto')
    $foto_db = ''; 
    $stmt_foto = $conn->prepare("SELECT foto FROM users WHERE id = ?");
    $stmt_foto->bind_param("i", $uid);
    $stmt_foto->execute();
    $res_foto = $stmt_foto->get_result();
    if ($res_foto->num_rows > 0) {
        $user_data = $res_foto->fetch_assoc();
        $foto_db = $user_data['foto'];
    }
    $stmt_foto->close();

    // LOGIKA BARU: Tentukan apakah menggunakan ikon atau gambar
    $use_icon = empty($foto_db) || $foto_db === 'student.png';

    // Set $foto_src hanya jika menggunakan gambar
    if (!$use_icon) {
        $foto_filename = $foto_db;
        $foto_src = $prefix . 'uploads/' . $foto_filename; 
    } else {
        $foto_src = ''; 
    }
}

// Check admin role
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['role']==='admin';

// Logika Brand Link
if ($is_admin) {
    $brand_link_html = $is_admin_page ? 'dashboard.php' : 'admin/dashboard.php';
} else {
    $brand_link_html = $prefix . 'index.php';
}

// Path untuk gambar logo
$image_src = $prefix . 'uploads/logo.png';
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
    min-width:30px;
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

/* ================= AVATAR STYLE BARU ================= */
#sidebar .user-avatar-container {
    padding: 0;
    width: 34px;
    min-width: 34px;
    height: 34px;
    border-radius: 50%;
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
#sidebar .user-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}
.avatar-icon-placeholder {
    font-size: 28px; /* Ukuran ikon yang sesuai */
    color: #6b7280; /* Warna netral */
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
/* ... (lanjutan CSS) ... */
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

    <a href="<?= $brand_link_html ?>" class="menu-item brand">
        <span class="icon">
            <img src="<?= $image_src ?>" alt="Preppy Finds">
        </span>
        <span class="text">Preppy Finds</span>
    </a>

<?php if(isset($_SESSION['user'])): ?>
    <a href="<?= $prefix ?>profile.php" class="menu-item" style="margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,.1);">
        <span class="user-avatar-container">
            <?php if($use_icon): ?>
                <i class="bi bi-person-circle avatar-icon-placeholder"></i>
            <?php else: ?>
                <img src="<?= $foto_src ?>" alt="Profile" class="user-avatar">
            <?php endif; ?>
        </span>
        <span class="text fw-bold"><?= htmlspecialchars($nama) ?></span>
    </a>
<?php endif; ?>


<?php if(isset($_SESSION['user']) && $_SESSION['user']['role']==='admin'): ?>

    <a href="<?= $prefix ?>admin/dashboard.php" class="menu-item">
        <span class="icon"><i class="bi bi-house"></i></span>
        <span class="text">Home</span>
    </a>

    <a href="<?= $prefix ?>search.php" class="menu-item">
        <span class="icon"><i class="bi bi-search"></i></span>
        <span class="text">Search</span>
    </a>

    <a href="<?= $prefix ?>jual.php" class="menu-item">
        <span class="icon"><i class="bi bi-plus-circle"></i></span>
        <span class="text">Jual</span>
    </a>

    <a href="<?= $prefix ?>inbox.php" class="menu-item">
        <span class="icon">
            <i class="bi bi-envelope"></i>
            <?php if($notif > 0): ?>
                <span class="badge-notif"><?= $notif ?></span>
            <?php endif; ?>
        </span>
        <span class="text">Inbox</span>
    </a>

    <a href="<?= $prefix ?>profile.php" class="menu-item">
        <span class="icon"><i class="bi bi-person"></i></span>
        <span class="text">Profile</span>
    </a>
    
    <a href="<?= $prefix ?>admin/manage_users.php" class="menu-item">
        <span class="icon"><i class="bi bi-people"></i></span>
        <span class="text">Manage Users</span>
    </a>

    <a href="<?= $prefix ?>admin/manage_items.php" class="menu-item">
        <span class="icon"><i class="bi bi-archive"></i></span>
        <span class="text">Manage Items</span>
    </a>

    <a href="<?= $prefix ?>logout.php" class="menu-item"
       onclick="return confirm('Yakin ingin logout? :(');">
        <span class="icon"><i class="bi bi-door-closed"></i></span>
        <span class="text">Logout</span>
    </a>

<?php elseif(isset($_SESSION['user'])): ?>

    <a href="index.php" class="menu-item">
        <span class="icon"><i class="bi bi-house"></i></span>
        <span class="text">Home</span>
    </a>

    <a href="search.php" class="menu-item">
        <span class="icon"><i class="bi bi-search"></i></span>
        <span class="text">Search</span>
    </a>

    <a href="jual.php" class="menu-item">
        <span class="icon"><i class="bi bi-plus-circle"></i></span>
        <span class="text">Jual</span>
    </a>

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

    <a href="logout.php" class="menu-item"
       onclick="return confirm('Yakin ingin logout? :(');">
        <span class="icon"><i class="bi bi-door-closed"></i></span>
        <span class="text">Logout</span>
    </a>

<?php else: ?>

    <a href="index.php" class="menu-item">
        <span class="icon"><i class="bi bi-house"></i></span>
        <span class="text">Home</span>
    </a>
    
    <a href="search.php" class="menu-item">
        <span class="icon"><i class="bi bi-search"></i></span>
        <span class="text">Search</span>
    </a>

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