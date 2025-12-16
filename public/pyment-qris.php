<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_login();

$pesanan_id = $_GET['id'] ?? 0;

// Get order
$stmt = $pdo->prepare("SELECT p.*, pw.nama_paket FROM pesanan p JOIN paket_wisata pw ON p.paket_id = pw.id WHERE p.id = ? AND p.user_id = ?");
$stmt->execute([$pesanan_id, get_user_id()]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['bukti']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = 'Format file tidak diizinkan! Gunakan JPG, PNG, atau PDF.';
        } elseif ($_FILES['bukti']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran file maksimal 5MB!';
        } else {
            $newname = 'payment_' . $pesanan_id . '_' . time() . '.' . $ext;
            $upload_path = UPLOAD_DIR . 'pembayaran/' . $newname;
            
            if (!is_dir(UPLOAD_DIR . 'pembayaran/')) {
                mkdir(UPLOAD_DIR . 'pembayaran/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("INSERT INTO pembayaran (pesanan_id, metode_pembayaran, jumlah, bukti_pembayaran, status) VALUES (?, 'qris', ?, ?, 'pending')");
                
                if ($stmt->execute([$pesanan_id, $pesanan['total_harga'], $newname])) {
                    alert('Bukti pembayaran berhasil diupload! Mohon tunggu konfirmasi admin.', 'success');
                    redirect('payment-status.php');
                } else {
                    $error = 'Gagal menyimpan data pembayaran.';
                }
            } else {
                $error = 'Gagal mengupload file.';
            }
        }
    } else {
        $error = 'Silakan pilih file bukti pembayaran!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand"><?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="payment-status.php">Pesanan Saya</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <a href="pyment.php?id=<?= $pesanan_id ?>" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Pilih Metode Lain</a>
            
            <h2 class="section-title">📱 Pembayaran QRIS</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info" style="text-align: center;">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">Scan QR Code</h3>
                        
                        <div style="padding: 2rem; background: #f8fafc; border-radius: 12px; margin-bottom: 2rem;">
                            <img src="assets/img/qris.jpg"  
                                 alt="QRIS Code" 
                                 style="max-width: 300px; width: 100%; border: 3px solid var(--primary); border-radius: 8px;"
                                 onerror="this.src='https://via.placeholder.com/300x300?text=QR+Code'">
                        </div>
                          
                        <div style="background: #e0f2fe; padding: 1.5rem; border-radius: 8px; text-align: left;">
                            <h4 style="color: var(--primary); margin-bottom: 1rem;">📋 Cara Pembayaran:</h4>
                            <ol style="color: var(--gray); line-height: 2;">
                                <li>Buka aplikasi e-wallet (GoPay, OVO, Dana, ShopeePay, dll)</li>
                                <li>Pilih menu "Scan QR" atau "Bayar"</li>
                                <li>Scan QR Code di atas</li>
                                <li>Masukkan nominal: <strong style="color: var(--primary);"><?= rupiah($pesanan['total_harga']) ?></strong></li>
                                <li>Konfirmasi pembayaran</li>
                                <li>Screenshot bukti pembayaran</li>
                                <li>Upload bukti di form sebelah kanan</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">📤 Upload Bukti Pembayaran</h3>
                        
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            <strong>⚠️ Penting:</strong>
                            <ul style="margin: 0.5rem 0 0 1.5rem;">
                                <li>Upload screenshot bukti transfer</li>
                                <li>Pastikan nominal dan waktu terlihat jelas</li>
                                <li>Format: JPG, PNG, atau PDF</li>
                                <li>Ukuran maksimal: 5MB</li>
                            </ul>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label">Pilih File Bukti Pembayaran</label>
                                <input type="file" name="bukti" class="form-control" 
                                       accept="image/*,.pdf" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Upload Bukti Pembayaran
                            </button>
                        </form>
                        
                        <hr style="margin: 2rem 0;">
                        
                        <h4 style="margin-bottom: 1rem;">Detail Pesanan</h4>
                        <div style="display: grid; gap: 0.75rem; font-size: 0.95rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Paket:</span>
                                <span style="font-weight: 600;"><?= htmlspecialchars($pesanan['nama_paket']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Pemesan:</span>
                                <span style="font-weight: 600;"><?= htmlspecialchars($pesanan['nama_pemesan']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Peserta:</span>
                                <span style="font-weight: 600;"><?= $pesanan['jumlah_peserta'] ?> orang</span>
                            </div>
                        </div>
                        
                        <hr style="margin: 1.5rem 0;">
                        
                        <div style="display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: bold; color: var(--primary);">
                            <span>Total:</span>
                            <span><?= rupiah($pesanan['total_harga']) ?></span>
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