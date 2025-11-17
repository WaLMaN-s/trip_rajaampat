<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

// Get all payments
$stmt = $pdo->query("
    SELECT pm.*, p.nama_pemesan, p.total_harga, pw.nama_paket, u.nama as user_nama
    FROM pembayaran pm
    JOIN pesanan p ON pm.pesanan_id = p.id
    JOIN paket_wisata pw ON p.paket_id = pw.id
    JOIN users u ON p.user_id = u.id
    ORDER BY pm.tanggal_upload DESC
");
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pembayaran - Admin <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">Admin - <?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="paket-list.php">Paket Wisata</a></li>
                <li><a href="galeri-list.php">Galeri</a></li>
                <li><a href="pembayaran-list.php">Pembayaran</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h2 class="section-title">💳 Daftar Pembayaran</h2>
            
            <?php show_alert(); ?>
            
            <div class="detail-info">
                <?php if (empty($payments)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada pembayaran.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pemesan</th>
                                    <th>Paket</th>
                                    <th>Metode</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Upload</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pm): ?>
                                <tr>
                                    <td>#<?= $pm['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($pm['nama_pemesan']) ?><br>
                                        <small style="color: var(--gray);"><?= htmlspecialchars($pm['user_nama']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($pm['nama_paket']) ?></td>
                                    <td>
                                        <?php if ($pm['metode_pembayaran'] === 'qris'): ?>
                                            <span style="color: var(--primary);">📱 QRIS</span>
                                        <?php else: ?>
                                            <span style="color: var(--secondary);">🏦 Transfer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= rupiah($pm['jumlah']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($pm['tanggal_upload'])) ?></td>
                                    <td>
                                        <?php if ($pm['status'] === 'pending'): ?>
                                            <span class="badge badge-pending">⏳ Pending</span>
                                        <?php elseif ($pm['status'] === 'valid'): ?>
                                            <span class="badge badge-valid">✓ Valid</span>
                                        <?php else: ?>
                                            <span class="badge badge-invalid">✗ Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="pembayaran-detail.php?id=<?= $pm['pesanan_id'] ?>" 
                                           class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?> - Admin Panel</p>
            </div>
        </div>
    </footer>
</body>
</html>