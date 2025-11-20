<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_login();

$pesanan_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $pdo->prepare("
    SELECT p.*, pw.nama_paket, pm.status as payment_status
    FROM pesanan p
    LEFT JOIN paket_wisata pw ON p.paket_id = pw.id
    LEFT JOIN pembayaran pm ON pm.pesanan_id = p.id
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$pesanan_id, get_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    alert('Pesanan tidak ditemukan!', 'danger');
    redirect('payment-status.php');
}

// Check if can cancel
$can_cancel = in_array($order['status'], ['pending', 'cancelled']) && 
              (!$order['payment_status'] || $order['payment_status'] === 'pending' || $order['payment_status'] === 'invalid');

if (!$can_cancel && $order['status'] === 'paid') {
    alert('Pesanan yang sudah dibayar tidak dapat dibatalkan. Hubungi admin untuk bantuan.', 'danger');
    redirect('payment-status.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = $_POST['alasan'] ?? '';
    
    if (empty($alasan)) {
        $error = 'Alasan pembatalan harus diisi!';
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Get full order details for logging
            $stmt = $pdo->prepare("
                SELECT p.*, pw.nama_paket, u.nama as user_nama, u.email as user_email
                FROM pesanan p
                LEFT JOIN paket_wisata pw ON p.paket_id = pw.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$pesanan_id]);
            $order_detail = $stmt->fetch();
            
            // Save to cancelled orders log
            $stmt = $pdo->prepare("
                INSERT INTO cancelled_orders_log 
                (pesanan_id, user_id, user_nama, user_email, paket_id, nama_paket, 
                 tanggal_berangkat, jumlah_peserta, total_harga, alasan_pembatalan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_detail['id'],
                $order_detail['user_id'],
                $order_detail['user_nama'],
                $order_detail['user_email'],
                $order_detail['paket_id'],
                $order_detail['nama_paket'],
                $order_detail['tanggal_berangkat'],
                $order_detail['jumlah_peserta'],
                $order_detail['total_harga'],
                $alasan
            ]);
            
            // Delete payment record first (if exists)
            $stmt = $pdo->prepare("DELETE FROM pembayaran WHERE pesanan_id = ?");
            $stmt->execute([$pesanan_id]);
            
            // Delete order
            $stmt = $pdo->prepare("DELETE FROM pesanan WHERE id = ?");
            $stmt->execute([$pesanan_id]);
            
            // Commit transaction
            $pdo->commit();
            
            alert('Pesanan berhasil dibatalkan dan dihapus!', 'success');
            redirect('payment-status.php');
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            $error = 'Gagal membatalkan pesanan! ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batalkan Pesanan - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand"><?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="payment-status.php">Pesanan Saya</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <a href="pyment-status.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Pesanan</a>
            
            <h2 class="section-title">🗑️ Batalkan & Hapus Pesanan</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (!$can_cancel): ?>
                <div class="alert alert-danger">
                    Pesanan ini tidak dapat dibatalkan karena sudah diverifikasi atau dalam proses. 
                    Silakan hubungi admin untuk bantuan lebih lanjut.
                </div>
                <a href="payment-status.php" class="btn btn-primary">Kembali ke Pesanan</a>
            <?php else: ?>
                <div class="detail-container">
                    <div>
                        <div class="detail-info">
                            <h3 style="color: var(--danger); margin-bottom: 1rem;">⚠️ Konfirmasi Pembatalan</h3>
                            
                            <div style="background: #fee2e2; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--danger); margin-bottom: 2rem;">
                                <strong>⚠️ Perhatian:</strong>
                                <ul style="margin: 0.5rem 0 0 1.5rem; line-height: 1.8;">
                                    <li><strong>Pesanan akan dihapus permanen</strong> dari sistem</li>
                                    <li>Data tidak dapat dikembalikan setelah dihapus</li>
                                    <li>Jika sudah transfer, segera hubungi admin untuk proses refund</li>
                                    <li>Pastikan alasan pembatalan jelas untuk keperluan refund</li>
                                </ul>
                            </div>
                            
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">Alasan Pembatalan *</label>
                                    <textarea name="alasan" class="form-control" required
                                              placeholder="Contoh: Berubah rencana, Salah pilih tanggal, Ada keperluan mendadak, dll"
                                              style="min-height: 120px;"></textarea>
                                    <small style="color: var(--gray);">Jelaskan alasan Anda membatalkan pesanan ini</small>
                                </div>
                                
                                <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" required
                                               style="width: 20px; height: 20px; cursor: pointer;">
                                        <span style="font-size: 0.95rem;"><strong>Saya yakin ingin membatalkan dan menghapus pesanan ini secara permanen</strong></span>
                                    </label>
                                </div>
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <a href="payment-status.php" class="btn btn-outline" style="text-align: center;">
                                        Kembali
                                    </a>
                                    <button type="submit" class="btn" 
                                            style="background: var(--danger); color: white;"
                                            onclick="return confirm('⚠️ PERINGATAN!\n\nPesanan ini akan DIHAPUS PERMANEN dari sistem!\nData tidak dapat dikembalikan.\n\nYakin ingin melanjutkan?')">
                                        🗑️ Batalkan & Hapus Pesanan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div>
                        <div class="detail-info">
                            <h3 style="color: var(--primary); margin-bottom: 1rem;">📋 Detail Pesanan</h3>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <div style="background: var(--danger); color: white; padding: 1rem; border-radius: 8px; text-align: center; margin-bottom: 1rem;">
                                    <div style="font-size: 0.9rem; opacity: 0.9;">Pesanan ID</div>
                                    <div style="font-size: 1.5rem; font-weight: bold;">#<?= $order['id'] ?></div>
                                </div>
                                
                                <h4><?= htmlspecialchars($order['nama_paket']) ?></h4>
                                
                                <hr style="margin: 1.5rem 0;">
                                
                                <div style="display: grid; gap: 0.75rem; font-size: 0.95rem;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--gray);">Nama Pemesan:</span>
                                        <span style="font-weight: 600;"><?= htmlspecialchars($order['nama_pemesan']) ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--gray);">Tanggal Berangkat:</span>
                                        <span style="font-weight: 600;"><?= date('d/m/Y', strtotime($order['tanggal_berangkat'])) ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--gray);">Jumlah Peserta:</span>
                                        <span style="font-weight: 600;"><?= $order['jumlah_peserta'] ?> orang</span>
                                    </div>
                                </div>
                                
                                <hr style="margin: 1.5rem 0;">
                                
                                <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: var(--primary);">
                                    <span>Total:</span>
                                    <span><?= rupiah($order['total_harga']) ?></span>
                                </div>
                                
                                <hr style="margin: 1.5rem 0;">
                                
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px;">
                                    <div style="font-size: 0.9rem; color: var(--gray); margin-bottom: 0.5rem;">Status Saat Ini:</div>
                                    <?php if (!$order['payment_status']): ?>
                                        <span class="badge badge-pending">Belum Bayar</span>
                                    <?php elseif ($order['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-pending">Menunggu Verifikasi</span>
                                    <?php elseif ($order['payment_status'] === 'invalid'): ?>
                                        <span class="badge badge-invalid">Pembayaran Ditolak</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="background: #e0f2fe; padding: 1rem; border-radius: 8px; font-size: 0.9rem;">
                                <strong>💡 Butuh Bantuan?</strong><br>
                                Hubungi: <a href="https://wa.me/6281234567890" style="color: var(--primary);">+62 812-3456-7890</a>
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
                <p>&copy; 2025 <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>