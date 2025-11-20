<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

$id = $_GET['id'] ?? 0;

// Get package details
$stmt = $pdo->prepare("SELECT * FROM paket_wisata WHERE id = ? AND tersedia = 1");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    redirect('index.php');
}

$fasilitas = explode(',', $package['fasilitas']);
$itinerary = explode('|', $package['itinerary']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($package['nama_paket']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand"><?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="paket.php">Paket Wisata</a></li>
            
                <?php if (is_logged_in()): ?>
                    <li><a href="pyment-status.php">Pesanan Saya</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Detail Section -->
    <section class="section">
        <div class="container">
            <a href="index.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Beranda</a>
            
            <?php show_alert(); ?>
            
            <div class="detail-container">
                <div>
                    <img src="uploads/paket/<?= htmlspecialchars($package['foto']) ?>" 
                         alt="<?= htmlspecialchars($package['nama_paket']) ?>" 
                         class="detail-img"
                         onerror="this.src='assets/img/placeholder.jpg'">
                    
                    <div class="detail-info" style="margin-top: 2rem;">
                        <h1><?= htmlspecialchars($package['nama_paket']) ?></h1>
                        <p style="color: var(--gray); margin: 1rem 0;"><?= nl2br(htmlspecialchars($package['deskripsi'])) ?></p>
                        
                        <h3 style="margin-top: 2rem; color: var(--primary);">📋 Fasilitas</h3>
                        <ul class="facility-list">
                            <?php foreach ($fasilitas as $item): ?>
                                <li><?= htmlspecialchars(trim($item)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <h3 style="margin-top: 2rem; color: var(--primary);">🗓️ Itinerary</h3>
                        <ul class="facility-list">
                            <?php foreach ($itinerary as $day): ?>
                                <li><?= htmlspecialchars(trim($day)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <div class="detail-price"><?= rupiah($package['harga']) ?></div>
                        <p style="color: var(--gray); margin-bottom: 1rem;">Per orang untuk <?= htmlspecialchars($package['durasi']) ?></p>
                        
                        <?php if (is_logged_in()): ?>
                            <a href="checkout.php?id=<?= $package['id'] ?>" class="btn btn-primary" style="width: 100%;">
                                Pesan Sekarang
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary" style="width: 100%;">
                                Login untuk Memesan
                            </a>
                        <?php endif; ?>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
                            <h4 style="margin-bottom: 1rem;">💬 Butuh Bantuan?</h4>
                            <p style="color: var(--gray); font-size: 0.9rem;">Hubungi kami untuk konsultasi gratis</p>
                            <a href="https://wa.me/6281234567890" class="btn btn-outline" style="width: 100%; margin-top: 1rem;">
                                Chat WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Raja Ampat Trip</h3>
                    <p>Penyedia layanan wisata terpercaya di Raja Ampat.</p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>📧 info@rajaampat.com</p>
                    <p>📱 +62 812-3456-7890</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>