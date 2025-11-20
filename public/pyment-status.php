<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_login();

// Get user orders
$stmt = $pdo->prepare("
    SELECT p.*, pw.nama_paket, pw.durasi, pm.status as payment_status, pm.keterangan, pm.tanggal_upload
    FROM pesanan p
    LEFT JOIN paket_wisata pw ON p.paket_id = pw.id
    LEFT JOIN pembayaran pm ON pm.pesanan_id = p.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([get_user_id()]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - <?= SITE_NAME ?></title>
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
            <h2 class="section-title">📋 Pesanan Saya</h2>
            
            <?php show_alert(); ?>
            
            <?php if (empty($orders)): ?>
                <div class="detail-info" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
                    <h3 style="color: var(--gray); margin-bottom: 1rem;">Belum ada pesanan</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Mulai jelajahi paket wisata kami dan buat pesanan pertama Anda!</p>
                    <a href="index.php#paket" class="btn btn-primary">Lihat Paket Wisata</a>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 2rem;">
                    <?php foreach ($orders as $order): ?>
                    <div class="detail-info">
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                            <div>
                                <div style="background: var(--primary); color: white; padding: 1rem; border-radius: 8px; text-align: center; margin-bottom: 1rem;">
                                    <div style="font-size: 0.9rem; opacity: 0.9;">Pesanan ID</div>
                                    <div style="font-size: 1.5rem; font-weight: bold;">#<?= $order['id'] ?></div>
                                </div>
                                
                                <div style="text-align: center;">
                                    <?php if (!$order['payment_status']): ?>
                                        <span class="badge badge-pending" style="display: block; padding: 0.75rem; font-size: 1rem;">⏳ Belum Bayar</span>
                                        <a href="payment.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                                            Bayar Sekarang
                                        </a>
                                        <a href="cancel-order.php?id=<?= $order['id'] ?>" 
                                           class="btn" 
                                           style="width: 100%; margin-top: 0.5rem; background: var(--danger); color: white;"
                                           onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            🚫 Batalkan Pesanan
                                        </a>
                                    <?php elseif ($order['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-pending" style="display: block; padding: 0.75rem; font-size: 1rem;">⏳ Menunggu Verifikasi</span>
                                        <p style="color: var(--gray); font-size: 0.85rem; margin-top: 0.5rem;">Bukti pembayaran sedang diverifikasi admin</p>
                                        <a href="cancel-order.php?id=<?= $order['id'] ?>" 
                                           class="btn" 
                                           style="width: 100%; margin-top: 1rem; background: var(--danger); color: white; font-size: 0.9rem;"
                                           onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            🚫 Batalkan Pesanan
                                        </a>
                                    <?php elseif ($order['payment_status'] === 'valid'): ?>
                                        <span class="badge badge-valid" style="display: block; padding: 0.75rem; font-size: 1rem;">✓ Pembayaran Valid</span>
                                        <p style="color: var(--success); font-size: 0.85rem; margin-top: 0.5rem;">Pesanan Anda dikonfirmasi!</p>
                                        <div style="background: #d1fae5; padding: 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.85rem; color: #065f46;">
                                            <strong>✓ Pesanan Dikonfirmasi</strong><br>
                                            Kami akan menghubungi Anda untuk detail lebih lanjut.
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-invalid" style="display: block; padding: 0.75rem; font-size: 1rem;">✗ Pembayaran Ditolak</span>
                                        <?php if ($order['keterangan']): ?>
                                            <div style="background: #fee2e2; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.85rem; text-align: left;">
                                                <strong>Alasan:</strong><br>
                                                <?= htmlspecialchars($order['keterangan']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <a href="payment.php?id=<?= $order['id'] ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                                            Upload Ulang
                                        </a>
                                        <a href="cancel-order.php?id=<?= $order['id'] ?>" 
                                           class="btn" 
                                           style="width: 100%; margin-top: 0.5rem; background: var(--danger); color: white; font-size: 0.9rem;"
                                           onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            🚫 Batalkan Pesanan
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <h3 style="color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($order['nama_paket']) ?></h3>
                                <p style="color: var(--gray); margin-bottom: 1.5rem;"><?= htmlspecialchars($order['durasi']) ?></p>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                    <div>
                                        <label style="color: var(--gray); font-size: 0.9rem;">Nama Pemesan</label>
                                        <p style="font-weight: 600;"><?= htmlspecialchars($order['nama_pemesan']) ?></p>
                                    </div>
                                    <div>
                                        <label style="color: var(--gray); font-size: 0.9rem;">No. HP</label>
                                        <p style="font-weight: 600;"><?= htmlspecialchars($order['no_hp_pemesan']) ?></p>
                                    </div>
                                    <div>
                                        <label style="color: var(--gray); font-size: 0.9rem;">Tanggal Berangkat</label>
                                        <p style="font-weight: 600;">📅 <?= date('d/m/Y', strtotime($order['tanggal_berangkat'])) ?></p>
                                    </div>
                                    <div>
                                        <label style="color: var(--gray); font-size: 0.9rem;">Jumlah Peserta</label>
                                        <p style="font-weight: 600;">👥 <?= $order['jumlah_peserta'] ?> orang</p>
                                    </div>
                                </div>
                                
                                <?php if ($order['catatan']): ?>
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                                    <label style="color: var(--gray); font-size: 0.9rem;">Catatan:</label>
                                    <p style="font-style: italic; font-size: 0.95rem;"><?= nl2br(htmlspecialchars($order['catatan'])) ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; justify-content: space-between; padding: 1rem; background: #f0f9ff; border-radius: 8px;">
                                    <span style="font-weight: 600;">Total Pembayaran:</span>
                                    <span style="font-size: 1.3rem; font-weight: bold; color: var(--primary);"><?= rupiah($order['total_harga']) ?></span>
                                </div>
                                
                                <div style="margin-top: 1rem; font-size: 0.85rem; color: var(--gray);">
                                    Dipesan pada: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                    <?php if ($order['tanggal_upload']): ?>
                                        <br>Bukti diupload: <?= date('d/m/Y H:i', strtotime($order['tanggal_upload'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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