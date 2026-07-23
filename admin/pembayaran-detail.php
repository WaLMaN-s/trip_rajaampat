<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

$pesanan_id = $_GET['id'] ?? 0;

// Get payment details
$stmt = $pdo->prepare("
    SELECT pm.*, p.*, pw.nama_paket, pw.durasi, u.nama as user_nama, u.email as user_email
    FROM pembayaran pm
    JOIN pesanan p ON pm.pesanan_id = p.id
    JOIN paket_wisata pw ON p.paket_id = pw.id
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$pesanan_id]);
$payment = $stmt->fetch();

if (!$payment) {
    alert('Data tidak ditemukan!', 'danger');
    redirect('pembayaran-list.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran - Admin <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main">

    <section class="section">
        <div class="container">
            <a href="pembayaran-list.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Daftar Pembayaran</a>
            
            <h2 class="section-title">💳 Detail & Verifikasi Pembayaran</h2>
            
            <?php show_alert(); ?>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📄 Bukti Pembayaran</h3>
                        
                        <?php if ($payment['bukti_pembayaran']): ?>
                            <?php
                            $file_ext = pathinfo($payment['bukti_pembayaran'], PATHINFO_EXTENSION);
                            $file_path = '../public/uploads/pembayaran/' . $payment['bukti_pembayaran'];
                            ?>
                            
                            <?php if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png'])): ?>
                                <img src="<?= $file_path ?>" alt="Bukti Pembayaran" 
                                     style="width: 100%; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div style="padding: 3rem; text-align: center; background: #f8fafc; border-radius: 8px;">
                                    <div style="font-size: 4rem; margin-bottom: 1rem;">📄</div>
                                    <p style="color: var(--gray); margin-bottom: 1rem;">File PDF</p>
                                    <a href="<?= $file_path ?>" target="_blank" class="btn btn-primary">
                                        Buka PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1rem; text-align: center;">
                                <a href="<?= $file_path ?>" download class="btn btn-outline" style="display: inline-block;">
                                    💾 Download Bukti
                                </a>
                            </div>
                        <?php else: ?>
                            <p style="text-align: center; color: var(--gray); padding: 3rem;">Bukti pembayaran belum diupload</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📋 Informasi Pesanan</h3>
                        
                        <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">ID Pesanan</label>
                                <p style="font-weight: 600;">#<?= $payment['pesanan_id'] ?></p>
                            </div>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">Paket Wisata</label>
                                <p style="font-weight: 600;"><?= htmlspecialchars($payment['nama_paket']) ?></p>
                                <p style="color: var(--gray); font-size: 0.9rem;"><?= htmlspecialchars($payment['durasi']) ?></p>
                            </div>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">Pemesan</label>
                                <p style="font-weight: 600;"><?= htmlspecialchars($payment['nama_pemesan']) ?></p>
                                <p style="font-size: 0.9rem;">📧 <?= htmlspecialchars($payment['email_pemesan']) ?></p>
                                <p style="font-size: 0.9rem;">📱 <?= htmlspecialchars($payment['no_hp_pemesan']) ?></p>
                            </div>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">User Account</label>
                                <p style="font-weight: 600;"><?= htmlspecialchars($payment['user_nama']) ?></p>
                                <p style="font-size: 0.9rem;"><?= htmlspecialchars($payment['user_email']) ?></p>
                            </div>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">Tanggal Keberangkatan</label>
                                <p style="font-weight: 600;">📅 <?= date('d F Y', strtotime($payment['tanggal_berangkat'])) ?></p>
                            </div>
                            
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">Jumlah Peserta</label>
                                <p style="font-weight: 600;">👥 <?= $payment['jumlah_peserta'] ?> orang</p>
                            </div>
                            
                            <?php if ($payment['catatan']): ?>
                            <div>
                                <label style="color: var(--gray); font-size: 0.9rem;">Catatan</label>
                                <p style="font-style: italic;"><?= nl2br(htmlspecialchars($payment['catatan'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <hr style="margin: 2rem 0;">
                        
                        <div style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Metode Pembayaran:</span>
                                <strong><?= strtoupper($payment['metode_pembayaran']) ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Tanggal Upload:</span>
                                <strong><?= date('d/m/Y H:i', strtotime($payment['tanggal_upload'])) ?></strong>
                            </div>
                            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: var(--primary);">
                                <span>Total:</span>
                                <span><?= rupiah($payment['total_harga']) ?></span>
                            </div>
                        </div>
                        
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                            <strong>Status Pembayaran:</strong>
                            <?php if ($payment['status'] === 'pending'): ?>
                                <span class="badge badge-pending" style="display: block; margin-top: 0.5rem;">⏳ Menunggu Verifikasi</span>
                            <?php elseif ($payment['status'] === 'valid'): ?>
                                <span class="badge badge-valid" style="display: block; margin-top: 0.5rem;">✓ Terverifikasi</span>
                                <p style="font-size: 0.85rem; margin-top: 0.5rem;">Diverifikasi: <?= date('d/m/Y H:i', strtotime($payment['tanggal_verifikasi'])) ?></p>
                            <?php else: ?>
                                <span class="badge badge-invalid" style="display: block; margin-top: 0.5rem;">✗ Ditolak</span>
                                <?php if ($payment['keterangan']): ?>
                                    <p style="font-size: 0.85rem; margin-top: 0.5rem;">Alasan: <?= htmlspecialchars($payment['keterangan']) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($payment['status'] === 'pending'): ?>
                            <div style="display: grid; gap: 1rem;">
                                <form action="pembayaran-valid.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="pesanan_id" value="<?= $payment['pesanan_id'] ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;" 
                                            onclick="return confirm('Verifikasi pembayaran ini sebagai VALID?')">
                                        ✓ Verifikasi Valid
                                    </button>
                                </form>
                                
                                <button onclick="showRejectForm()" class="btn" 
                                        style="width: 100%; background: var(--danger); color: white;">
                                    ✗ Tolak Pembayaran
                                </button>
                                
                                <div id="rejectForm" style="display: none; padding: 1rem; background: #fee2e2; border-radius: 8px; margin-top: 1rem;">
                                    <form action="pembayaran-invalid.php" method="POST">
                                        <input type="hidden" name="pesanan_id" value="<?= $payment['pesanan_id'] ?>">
                                        <div class="form-group">
                                            <label class="form-label">Alasan Penolakan</label>
                                            <textarea name="keterangan" class="form-control" required 
                                                      placeholder="Contoh: Nominal tidak sesuai, bukti tidak jelas, dll"></textarea>
                                        </div>
                                        <button type="submit" class="btn" style="width: 100%; background: var(--danger); color: white;">
                                            Kirim Penolakan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    function showRejectForm() {
        document.getElementById('rejectForm').style.display = 'block';
    }
    </script>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?> - Admin Panel</p>
            </div>
        </div>
    </footer>
    </div><!-- .admin-main -->
</body>
</html>