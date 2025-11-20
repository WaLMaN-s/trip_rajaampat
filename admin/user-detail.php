<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

$user_id = $_GET['id'] ?? 0;

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    alert('User tidak ditemukan!', 'danger');
    redirect('user-list.php');
}

// Get user orders
$stmt = $pdo->prepare("
    SELECT p.*, pw.nama_paket, pm.status as payment_status, pm.jumlah as payment_amount
    FROM pesanan p
    LEFT JOIN paket_wisata pw ON p.paket_id = pw.id
    LEFT JOIN pembayaran pm ON pm.pesanan_id = p.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Calculate stats
$total_orders = count($orders);
$paid_orders = count(array_filter($orders, fn($o) => $o['payment_status'] === 'valid'));
$total_spent = array_sum(array_column(array_filter($orders, fn($o) => $o['payment_status'] === 'valid'), 'payment_amount'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - Admin <?= SITE_NAME ?></title>
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
            <a href="user-list.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Daftar User</a>
            
            <h2 class="section-title">👤 Detail User</h2>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📋 Informasi User</h3>
                        
                        <div style="text-align: center; margin-bottom: 2rem; padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 12px; color: white;">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">👤</div>
                            <h2 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($user['nama']) ?></h2>
                            <p style="opacity: 0.9;">User ID: #<?= $user['id'] ?></p>
                        </div>
                        
                        <div style="display: grid; gap: 1.5rem;">
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">📧 Email</label>
                                <strong style="font-size: 1.1rem;"><?= htmlspecialchars($user['email']) ?></strong>
                            </div>
                            
                            <?php if ($user['no_hp']): ?>
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">📱 No. HP</label>
                                <strong style="font-size: 1.1rem;"><?= htmlspecialchars($user['no_hp']) ?></strong>
                                <a href="https://wa.me/<?= preg_replace('/[^0-7]/', '', $user['no_hp']) ?>" 
                                   target="_blank"
                                   class="btn btn-primary" 
                                   style="display: inline-block; margin-top: 0.rem; padding: 0.5rem ; font-size: 0.5rem;">
                                    💬 Chat WhatsApp
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user['alamat']): ?>
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">📍 Alamat</label>
                                <p style="margin: 0;"><?= nl2br(htmlspecialchars($user['alamat'])) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">📅 Tanggal Daftar</label>
                                <strong><?= date('d F Y, H:i', strtotime($user['created_at'])) ?> WIB</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📊 Statistik</h3>
                        
                        <div style="display: grid; gap: 1rem;">
                            <div style="padding: 1.5rem; background: #f0f9ff; border-radius: 8px; text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary);"><?= $total_orders ?></div>
                                <p style="margin: 0; color: var(--gray);">Total Pesanan</p>
                            </div>
                            
                            <div style="padding: 1.5rem; background: #d1fae5; border-radius: 8px; text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: bold; color: var(--success);"><?= $paid_orders ?></div>
                                <p style="margin: 0; color: var(--gray);">Pesanan Sukses</p>
                            </div>
                            
                            <div style="padding: 1.5rem; background: #fef3c7; border-radius: 8px; text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: var(--secondary);"><?= rupiah($total_spent) ?></div>
                                <p style="margin: 0; color: var(--gray);">Total Spending</p>
                            </div>
                        </div>
                        
                        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 8px;">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">💎 Status Customer</h4>
                            <?php if ($paid_orders >= 5): ?>
                                <div class="badge" style="background: gold; color: #92400e; padding: 0.75rem 1.5rem; font-size: 1rem;">
                                    ⭐ VIP Customer
                                </div>
                            <?php elseif ($paid_orders >= 2): ?>
                                <div class="badge badge-valid" style="padding: 0.75rem 1.5rem; font-size: 1rem;">
                                    ✓ Loyal Customer
                                </div>
                            <?php elseif ($paid_orders >= 1): ?>
                                <div class="badge" style="background: #dbeafe; color: #1e40af; padding: 0.75rem 1.5rem; font-size: 1rem;">
                                    👍 Active Customer
                                </div>
                            <?php else: ?>
                                <div class="badge badge-pending" style="padding: 0.75rem 1.5rem; font-size: 1rem;">
                                    🆕 New User
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order History -->
            <div class="detail-info" style="margin-top: 3rem;">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📦 Riwayat Pesanan</h3>
                
                <?php if (empty($orders)): ?>
                    <p style="text-align: center; color: var(--gray); padding: 2rem;">User ini belum pernah melakukan pesanan.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paket</th>
                                    <th>Tanggal</th>
                                    <th>Peserta</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['nama_paket']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['tanggal_berangkat'])) ?></td>
                                    <td><?= $order['jumlah_peserta'] ?> orang</td>
                                    <td><?= rupiah($order['total_harga']) ?></td>
                                    <td>
                                        <?php if (!$order['payment_status']): ?>
                                            <span class="badge badge-pending">Belum Bayar</span>
                                        <?php elseif ($order['payment_status'] === 'pending'): ?>
                                            <span class="badge badge-pending">Pending</span>
                                        <?php elseif ($order['payment_status'] === 'valid'): ?>
                                            <span class="badge badge-valid">Sukses</span>
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