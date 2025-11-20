<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

// Redirect jika sudah login
if (is_logged_in()) {
    if (is_admin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Cari user berdasarkan email (tidak peduli role)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            set_user_session($user);
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                alert('Login berhasil! Selamat datang Admin ' . $user['nama'], 'success');
                redirect('../admin/dashboard.php');
            } else {
                alert('Login berhasil! Selamat datang ' . $user['nama'], 'success');
                redirect('index.php');
            }
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title">🏝️ Login</h2>
            <p style="text-align: center; color: var(--gray); margin-bottom: 2rem;">
                Masuk untuk memesan paket wisata
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="nama@email.com" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
                Belum punya akun? 
                <a href="register.php" style="color: var(--primary); font-weight: 600;">Daftar di sini</a>
            </p>
            
            <p style="text-align: center; margin-top: 1rem;">
                <a href="index.php" style="color: var(--gray);">← Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</body>
</html>