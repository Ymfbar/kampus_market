<?php 
include 'includes/header.php'; // session_start sudah ada
?>

<style>
/* ===== REGISTER UI ===== */
.register-wrapper{
    min-height: calc(100vh - 120px);
    display:flex;
    align-items:center;
    justify-content:center;
    background:
        linear-gradient(
            rgba(0,0,0,.35),
            rgba(0,0,0,.35)
        ),
        url("uploads/247.jpg") center/cover no-repeat;
}

.register-card{
    width:100%;
    max-width:420px;
    background:#fff;
    border-radius:18px;
    padding:32px 28px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

.register-card h3{
    font-weight:700;
    text-align:center;
    margin-bottom:8px;
}

.register-card p{
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

.btn-register{
    background:#6f2cff;
    border:none;
    border-radius:999px;
    padding:12px;
    font-weight:600;
}

.btn-register:hover{
    background:#5a23d8;
}

.register-footer{
    text-align:center;
    margin-top:18px;
    font-size:13px;
}

.register-footer a{
    color:#6f2cff;
    text-decoration:none;
    font-weight:600;
}
</style>

<div class="register-wrapper">
    <div class="register-card">

        <h3>Create Account ✨</h3>
        <p>Gabung di <strong>Preppy Finds</strong></p>

        <?php
        $msg = '';
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $nama = trim($_POST['nama']);
            $email = trim($_POST['email']);
            $pass = $_POST['password'];

            if($nama && $email && $pass){
                $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
                $stmt->bind_param('s',$email);
                $stmt->execute();
                $stmt->store_result();

                if($stmt->num_rows > 0){
                    $msg = "Email sudah terdaftar.";
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $stmt2 = $conn->prepare(
                        "INSERT INTO users (nama,email,password) VALUES (?,?,?)"
                    );
                    $stmt2->bind_param('sss',$nama,$email,$hash);

                    if($stmt2->execute()){
                        $msg = 'Registrasi berhasil. 
                        <a href="login.php" class="fw-bold text-decoration-none" style="color:#6f2cff">
                            Login sekarang
                        </a>';
                    } else {
                        $msg = "Gagal registrasi.";
                    }
                }
            } else {
                $msg = "Lengkapi semua field.";
            }
        }

        if($msg): ?>
            <div class="alert alert-info text-center"><?= $msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input name="nama" class="form-control" placeholder="Full name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button class="btn btn-register w-100">Create Account</button>
        </form>

        <div class="register-footer">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
