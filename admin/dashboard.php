<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

// Get statistics
$stats = [];

// Total paket
$stmt = $pdo->query("SELECT COUNT(*) as total FROM paket_wisata WHERE tersedia = 1");
$stats['paket'] = $stmt->fetch()['total'];

// Total galeri
$stmt = $pdo->query("SELECT COUNT(*) as total FROM galeri WHERE tampilkan = 1");
$stats['galeri'] = $stmt->fetch()['total'];

// Total pesanan
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pesanan");
$stats['pesanan'] = $stmt->fetch()['total'];

// Pembayaran pending
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pembayaran WHERE status = 'pending'");
$stats['pending'] = $stmt->fetch()['total'];

// Total revenue (validated payments)
$stmt = $pdo->query("SELECT SUM(jumlah) as total FROM pembayaran WHERE status = 'valid'");
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch()['total'];

// Recent orders
$stmt = $pdo->query("
    SELECT p.*, pw.nama_paket, u.nama as user_nama, pm.status as payment_status
    FROM pesanan p
    LEFT JOIN paket_wisata pw ON p.paket_id = pw.id
    LEFT JOIN users u ON p.user_id = u.id
    LEFT JOIN pembayaran pm ON pm.pesanan_id = p.id
    ORDER BY p.created_at DESC
    LIMIT 10
");
$recent_orders = $stmt->fetchAll();

// Recent registered users
$stmt = $pdo->query("
    SELECT id, nama, email, no_hp, created_at
    FROM users 
    WHERE role = 'user'
    ORDER BY created_at DESC
    LIMIT 10
");
$recent_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin <?= SITE_NAME ?></title>
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
                <li><a href="user-list.php">User</a></li>
                <li><a href="pembayaran-list.php">Pembayaran</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <h2 class="section-title">📊 Dashboard Administrator</h2>
            
            <?php show_alert(); ?>
            
            <!-- Statistics Cards -->
            <div class="card-grid" style="margin-bottom: 3rem;">
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📦</div>
                        <h3 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;"><?= $stats['paket'] ?></h3>
                        <p style="color: var(--gray);">Paket Wisata Aktif</p>
                        <a href="paket-list.php" style="color: var(--primary); font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">Kelola Paket →</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📸</div>
                        <h3 style="font-size: 2.5rem; color: #8b5cf6; margin-bottom: 0.5rem;"><?= $stats['galeri'] ?></h3>
                        <p style="color: var(--gray);">Foto di Galeri</p>
                        <a href="galeri-list.php" style="color: #8b5cf6; font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">Kelola Galeri →</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎫</div>
                        <h3 style="font-size: 2.5rem; color: var(--secondary); margin-bottom: 0.5rem;"><?= $stats['pesanan'] ?></h3>
                        <p style="color: var(--gray);">Total Pesanan</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">⏳</div>
                        <h3 style="font-size: 2.5rem; color: #f59e0b; margin-bottom: 0.5rem;"><?= $stats['pending'] ?></h3>
                        <p style="color: var(--gray);">Menunggu Verifikasi</p>
                        <?php if ($stats['pending'] > 0): ?>
                            <a href="pembayaran-list.php" style="color: var(--primary); font-size: 0.9rem;">Lihat Detail →</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">💰</div>
                        <h3 style="font-size: 1.8rem; color: var(--success); margin-bottom: 0.5rem;"><?= rupiah($stats['revenue']) ?></h3>
                        <p style="color: var(--gray);">Total Pendapatan</p>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">👥</div>
                        <h3 style="font-size: 2.5rem; color: #06b6d4; margin-bottom: 0.5rem;"><?= $stats['users'] ?></h3>
                        <p style="color: var(--gray);">Total User Terdaftar</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="detail-info">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📋 Pesanan Terbaru</h3>
                
                <?php if (empty($recent_orders)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 2rem;">Belum ada pesanan.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paket</th>
                                    <th>Pemesan</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['nama_paket']) ?></td>
                                    <td><?= htmlspecialchars($order['nama_pemesan']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['tanggal_berangkat'])) ?></td>
                                    <td><?= rupiah($order['total_harga']) ?></td>
                                    <td>
                                        <?php if (!$order['payment_status']): ?>
                                            <span class="badge badge-pending">Belum Bayar</span>
                                        <?php elseif ($order['payment_status'] === 'pending'): ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php elseif ($order['payment_status'] === 'valid'): ?>
                                            <span class="badge badge-valid">Valid</span>
                                        <?php else: ?>
                                            <span class="badge badge-invalid">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                            <a href="pembayaran-detail.php?id=<?= $order['id'] ?>" 
                                               class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                                Verifikasi
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Users -->
            <div class="detail-info" style="margin-top: 3rem;">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">👥 User Terdaftar Terbaru</h3>
                
                <?php if (empty($recent_users)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 2rem;">Belum ada user terdaftar.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. HP</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td>#<?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['nama']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= $user['no_hp'] ? htmlspecialchars($user['no_hp']) : '-' ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        // Check if user has orders
                                        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pesanan WHERE user_id = ?");
                                        $stmt->execute([$user['id']]);
                                        $order_count = $stmt->fetch()['total'];
                                        ?>
                                        <?php if ($order_count > 0): ?>
                                            <span class="badge badge-valid"><?= $order_count ?> Pesanan</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Belum Pesan</span>
                                        <?php endif; ?>
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