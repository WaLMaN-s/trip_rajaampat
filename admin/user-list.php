<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

// Get all users
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT p.id) as total_pesanan,
           SUM(CASE WHEN pm.status = 'valid' THEN pm.jumlah ELSE 0 END) as total_spending
    FROM users u
    LEFT JOIN pesanan p ON u.id = p.user_id
    LEFT JOIN pembayaran pm ON p.id = pm.pesanan_id
    WHERE u.role = 'user'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User - Admin <?= SITE_NAME ?></title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 class="section-title" style="margin-bottom: 0;">👥 Daftar User Terdaftar</h2>
                <div style="background: #f0f9ff; padding: 1rem 1.5rem; border-radius: 8px;">
                    <strong style="color: var(--primary);">Total: <?= count($users) ?> User</strong>
                </div>
            </div>
            
            <?php show_alert(); ?>
            
            <?php if (empty($users)): ?>
                <div class="detail-info" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">👥</div>
                    <h3 style="color: var(--gray); margin-bottom: 1rem;">Belum ada user terdaftar</h3>
                    <p style="color: var(--gray);">User akan muncul di sini setelah melakukan registrasi</p>
                </div>
            <?php else: ?>
                <div class="detail-info">
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                   
                                    <th>Nama</th>
                                    <th>Kontak</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Total Pesanan</th>
                                    <th>Total Spending</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                   
                                    <td>
                                        <strong><?= htmlspecialchars($user['nama']) ?></strong><br>
                                        <?php if ($user['alamat']): ?>
                                            <small style="color: var(--gray);">📍 <?= htmlspecialchars(substr($user['alamat'], 0, 30)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            📧 <?= htmlspecialchars($user['email']) ?><br>
                                            <?php if ($user['no_hp']): ?>
                                                📱 <?= htmlspecialchars($user['no_hp']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td style="text-align: center;">
                                        <?php if ($user['total_pesanan'] > 0): ?>
                                            <span class="badge badge-valid"><?= $user['total_pesanan'] ?> Pesanan</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">Belum Pesan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user['total_spending'] > 0): ?>
                                            <strong style="color: var(--success);"><?= rupiah($user['total_spending']) ?></strong>
                                        <?php else: ?>
                                            <span style="color: var(--gray);">Rp 0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="user-detail.php?id=<?= $user['id'] ?>" 
                                           class="btn btn-primary" 
                                           style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        
                    </div>
                </div>
            <?php endif; ?>
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