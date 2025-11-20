<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_login();

$pesanan_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $pdo->prepare("
    SELECT p.*, pw.nama_paket, pw.durasi, pw.foto
    FROM pesanan p
    JOIN paket_wisata pw ON p.paket_id = pw.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$pesanan_id, get_user_id()]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    alert('Pesanan tidak ditemukan!', 'danger');
    redirect('index.php');
}

// Check if already paid
$stmt = $pdo->prepare("SELECT * FROM pembayaran WHERE pesanan_id = ?");
$stmt->execute([$pesanan_id]);
$pembayaran = $stmt->fetch();

if ($pembayaran) {
    redirect("pyment-status.php");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand"><?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="pyment-status.php">Pesanan Saya</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h2 class="section-title">💳 Pilih Metode Pembayaran</h2>
            
            <?php show_alert(); ?>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">Metode Pembayaran</h3>
                        
                        <div style="display: grid; gap: 1.5rem;">
                            <a href="pyment-qris.php?id=<?= $pesanan_id ?>" 
                               style="display: block; padding: 2rem; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; transition: all 0.3s;"
                               onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f0f9ff';"
                               onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white';">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
                                <h3 style="color: var(--dark); margin-bottom: 0.5rem;">QRIS</h3>
                                <p style="color: var(--gray);">Scan QR Code dengan aplikasi e-wallet Anda</p>
                            </a>
                            
                            <a href="pyment-transfer.php?id=<?= $pesanan_id ?>" 
                               style="display: block; padding: 2rem; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; transition: all 0.3s;"
                               onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#f0f9ff';"
                               onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='white';">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🏦</div>
                                <h3 style="color: var(--dark); margin-bottom: 0.5rem;">Transfer Bank</h3>
                                <p style="color: var(--gray);">Transfer ke rekening bank kami</p>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">📋 Ringkasan Pesanan</h3>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <img src="uploads/paket/<?= htmlspecialchars($pesanan['foto']) ?>" 
                                 alt="<?= htmlspecialchars($pesanan['nama_paket']) ?>"
                                 style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;"
                                 onerror="this.src='assets/img/placeholder.jpg'">
                        </div>
                        
                        <h4><?= htmlspecialchars($pesanan['nama_paket']) ?></h4>
                        <p style="color: var(--gray); margin: 0.5rem 0;"><?= htmlspecialchars($pesanan['durasi']) ?></p>
                        
                        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                        
                        <div style="display: grid; gap: 0.75rem; font-size: 0.95rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Nama Pemesan:</span>
                                <span style="font-weight: 600;"><?= htmlspecialchars($pesanan['nama_pemesan']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Tanggal Berangkat:</span>
                                <span style="font-weight: 600;"><?= date('d/m/Y', strtotime($pesanan['tanggal_berangkat'])) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Jumlah Peserta:</span>
                                <span style="font-weight: 600;"><?= $pesanan['jumlah_peserta'] ?> orang</span>
                            </div>
                        </div>
                        
                        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                        
                        <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: var(--primary);">
                            <span>Total Pembayaran:</span>
                            <span><?= rupiah($pesanan['total_harga']) ?></span>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #fef3c7; border-radius: 8px; font-size: 0.9rem;">
                            <strong>⏰ Selesaikan pembayaran dalam 24 jam</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>