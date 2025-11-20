<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

// Get cancelled orders log
$stmt = $pdo->query("
    SELECT * FROM cancelled_orders_log 
    ORDER BY cancelled_at DESC
");
$cancelled_orders = $stmt->fetchAll();

// Get statistics
$total_cancelled = count($cancelled_orders);
$total_lost_revenue = array_sum(array_column($cancelled_orders, 'total_harga'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Pesanan Dibatalkan - Admin <?= SITE_NAME ?></title>
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
                <h2 class="section-title" style="margin-bottom: 0;">🗑️ Log Pesanan Dibatalkan</h2>
                <div style="text-align: right;">
                    <div style="background: #fee2e2; padding: 1rem 1.5rem; border-radius: 8px; display: inline-block;">
                        <div style="font-size: 0.9rem; color: var(--gray);">Total Dibatalkan</div>
                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--danger);"><?= $total_cancelled ?> Pesanan</div>
                    </div>
                </div>
            </div>
            
            <?php show_alert(); ?>
            
            <?php if (empty($cancelled_orders)): ?>
                <div class="detail-info" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                    <h3 style="color: var(--success); margin-bottom: 1rem;">Tidak Ada Pembatalan</h3>
                    <p style="color: var(--gray);">Belum ada pesanan yang dibatalkan. Semua pesanan berjalan lancar!</p>
                </div>
            <?php else: ?>
                <!-- Statistics Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="detail-info">
                        <div style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🗑️</div>
                            <div style="font-size: 2rem; font-weight: bold; color: var(--danger);"><?= $total_cancelled ?></div>
                            <p style="margin: 0; color: var(--gray);">Total Pesanan Dibatalkan</p>
                        </div>
                    </div>
                    
                    <div class="detail-info">
                        <div style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">💸</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: var(--danger);"><?= rupiah($total_lost_revenue) ?></div>
                            <p style="margin: 0; color: var(--gray);">Potensi Revenue Hilang</p>
                        </div>
                    </div>
                    
                    <div class="detail-info">
                        <div style="text-align: center; padding: 1.5rem;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                            <div style="font-size: 2rem; font-weight: bold; color: var(--primary);">
                                <?= $total_cancelled > 0 ? number_format(($total_cancelled / ($total_cancelled + 10)) * 100, 1) : 0 ?>%
                            </div>
                            <p style="margin: 0; color: var(--gray);">Estimasi Cancel Rate</p>
                        </div>
                    </div>
                </div>
                
                <div class="detail-info">
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>User</th>
                                    <th>Paket</th>
                                    <th>Tanggal</th>
                                    <th>Peserta</th>
                                    <th>Total</th>
                                    <th>Alasan</th>
                                    <th>Dibatalkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cancelled_orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order['pesanan_id'] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['user_nama']) ?></strong><br>
                                        <small style="color: var(--gray);"><?= htmlspecialchars($order['user_email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($order['nama_paket']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['tanggal_berangkat'])) ?></td>
                                    <td><?= $order['jumlah_peserta'] ?> orang</td>
                                    <td style="color: var(--danger); font-weight: 600;"><?= rupiah($order['total_harga']) ?></td>
                                    <td>
                                        <div style="max-width: 200px;">
                                            <?= htmlspecialchars(substr($order['alasan_pembatalan'], 0, 50)) ?>
                                            <?php if (strlen($order['alasan_pembatalan']) > 50): ?>
                                                <a href="#" onclick="alert('<?= htmlspecialchars(addslashes($order['alasan_pembatalan'])) ?>'); return false;" 
                                                   style="color: var(--primary);">...selengkapnya</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($order['cancelled_at'])) ?><br>
                                        <small style="color: var(--gray);"><?= time_ago($order['cancelled_at']) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; padding: 1.5rem; background: #fef3c7; border-radius: 8px;">
                    <strong>💡 Tips Mengurangi Pembatalan:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem; color: var(--dark);">
                        <li>Pastikan deskripsi paket jelas dan detail</li>
                        <li>Berikan kebijakan pembatalan yang fleksibel</li>
                        <li>Follow up cepat setelah booking</li>
                        <li>Reminder otomatis sebelum tanggal keberangkatan</li>
                        <li>Customer service yang responsif</li>
                    </ul>
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