<?php
session_start();
include 'includes/config.php';

/* ================= PROSES LOGIN ================= */
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $conn->prepare("SELECT id,nama,email,password,role FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1){
        $u = $res->fetch_assoc();
        if(password_verify($pass, $u['password'])){
            $_SESSION['user'] = [
                'id'    => $u['id'],
                'nama'  => $u['nama'],
                'email' => $u['email'],
                'role'  => $u['role']
            ];

            header("Location: ".($u['role']==='admin' ? 'admin/dashboard.php' : 'dashboard.php'));
            exit;
        } else {
            $_SESSION['flash_msg'] = "Password salah.";
        }
    } else {
        $_SESSION['flash_msg'] = "Email belum terdaftar.";
    }

    header("Location: login.php");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<style>
/* ===== LOGIN UI ===== */
.login-wrapper{
    min-height: calc(100vh - 120px);
    display:flex;
    align-items:center;
    justify-content:center;
    background:
        linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)),
        url("uploads/247.jpg") center/cover no-repeat;
}

.login-card{
    width:100%;
    max-width:420px;
    background:#fff;
    border-radius:18px;
    padding:32px 28px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

.login-card h3{
    font-weight:700;
    text-align:center;
    margin-bottom:8px;
}

.login-card p{
    text-align:center;
    color:#6b7280;
    font-size:14px;
    margin-bottom:24px;
}

.form-control{
    border-radius:12px;
    padding:12px 14px;
    font-size:14px;
}

.form-control:focus{
    box-shadow:0 0 0 .2rem rgba(111,44,255,.15);
    border-color:#6f2cff;
}

.btn-login{
    background:#6f2cff;
    border:none;
    border-radius:999px;
    padding:12px;
    font-weight:600;
    color:#fff;
}

.btn-login:hover{
    background:#5a23d8;
}

.login-footer{
    text-align:center;
    margin-top:18px;
    font-size:13px;
}

.login-footer a{
    color:#6f2cff;
    text-decoration:none;
    font-weight:600;
}
</style>

<div class="login-wrapper">
    <div class="login-card">

        <h3>Welcome Back 👋</h3>
        <p>Login ke <strong>Preppy Finds</strong></p>

        <?php if(isset($_SESSION['flash_msg'])): ?>
            <div class="alert alert-warning text-center">
                <?= $_SESSION['flash_msg']; unset($_SESSION['flash_msg']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button class="btn btn-login w-100">Login</button>
        </form>

        <div class="login-footer">
            Belum punya akun? <a href="register.php">Daftar</a>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
