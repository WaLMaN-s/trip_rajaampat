<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    
    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password harus diisi!';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'user')");
            
            if ($stmt->execute([$nama, $email, $hashed, $no_hp])) {
                alert('Registrasi berhasil! Silakan login.', 'success');
                redirect('login.php');
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2 class="auth-title">🏝️ Daftar Akun</h2>
            <p style="text-align: center; color: var(--gray); margin-bottom: 2rem;">
                Buat akun untuk mulai memesan paket wisata
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" 
                           placeholder="Nama lengkap Anda" required
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="nama@email.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. HP (Opsional)</label>
                    <input type="tel" name="no_hp" class="form-control" 
                           placeholder="08xxxxxxxxxx"
                           value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Minimal 6 karakter" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" 
                           placeholder="Ulangi password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Daftar Sekarang
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
                Sudah punya akun? 
                <a href="login.php" style="color: var(--primary); font-weight: 600;">Login di sini</a>
            </p>
            
            <p style="text-align: center; margin-top: 1rem;">
                <a href="index.php" style="color: var(--gray);">← Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</body>
</html>