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
                $stmt = $pdo->prepare("INSERT INTO pembayaran (pesanan_id, metode_pembayaran, jumlah, bukti_pembayaran, status) VALUES (?, 'transfer', ?, ?, 'pending')");
                
                if ($stmt->execute([$pesanan_id, $pesanan['total_harga'], $newname])) {
                    alert('Bukti pembayaran berhasil diupload! Mohon tunggu konfirmasi admin.', 'success');
                    redirect('pyment-status.php');
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
    <title>Pembayaran Transfer Bank - <?= SITE_NAME ?></title>
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
            <a href="payment.php?id=<?= $pesanan_id ?>" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Pilih Metode Lain</a>
            
            <h2 class="section-title">🏦 Pembayaran Transfer Bank</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1.5rem; text-align: center;">Informasi Rekening</h3>
                        
                        <div style="padding: 2rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 16px; color: white; margin-bottom: 2rem; box-shadow: 0 8px 24px rgba(8,145,178,0.3);">
                            <div style="text-align: center; margin-bottom: 1.5rem;">
                                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🏦</div>
                                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?= BANK_NAME ?></h3>
                            </div>
                            
                            <div style="background: rgba(255,255,255,0.15); padding: 1.5rem; border-radius: 12px; backdrop-filter: blur(10px);">
                                <div style="text-align: center; margin-bottom: 1rem;">
                                    <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">Nomor Rekening</p>
                                    <p style="font-size: 2rem; font-weight: bold; letter-spacing: 2px; font-family: 'Courier New', monospace;"><?= BANK_ACCOUNT ?></p>
                                </div>
                                <div style="text-align: center; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.3);">
                                    <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem;">Atas Nama</p>
                                    <p style="font-size: 1.3rem; font-weight: bold;"><?= BANK_HOLDER ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #e0f2fe; padding: 1.5rem; border-radius: 8px; text-align: left;">
                            <h4 style="color: var(--primary); margin-bottom: 1rem;">📋 Cara Pembayaran:</h4>
                            <ol style="color: var(--dark); line-height: 2;">
                                <li>Buka aplikasi mobile banking atau m-banking Anda</li>
                                <li>Pilih menu "Transfer" atau "Transfer Bank"</li>
                                <li>Pilih bank tujuan: <strong><?= BANK_NAME ?></strong></li>
                                <li>Masukkan nomor rekening: <strong><?= BANK_ACCOUNT ?></strong></li>
                                <li>Masukkan nominal: <strong style="color: var(--primary);"><?= rupiah($pesanan['total_harga']) ?></strong></li>
                                <li>Pastikan nama penerima: <strong><?= BANK_HOLDER ?></strong></li>
                                <li>Konfirmasi dan lakukan transfer</li>
                                <li>Screenshot bukti transfer</li>
                                <li>Upload bukti di form sebelah kanan</li>
                            </ol>
                        </div>
                        
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; font-size: 0.9rem;">
                            <strong>⚠️ Penting:</strong>
                            <ul style="margin: 0.5rem 0 0 1.5rem;">
                                <li>Transfer sesuai nominal <strong>PERSIS</strong></li>
                                <li>Cek kembali nomor rekening dan nama penerima</li>
                                <li>Simpan bukti transfer untuk keperluan klaim</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">📤 Upload Bukti Pembayaran</h3>
                        
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            <strong>⚠️ Pastikan:</strong>
                            <ul style="margin: 0.5rem 0 0 1.5rem;">
                                <li>Screenshot menampilkan:
                                    <ul style="margin-left: 1rem;">
                                        <li>Nominal transfer</li>
                                        <li>Nomor rekening tujuan</li>
                                        <li>Nama penerima</li>
                                        <li>Tanggal & waktu transfer</li>
                                        <li>Status "Berhasil"</li>
                                    </ul>
                                </li>
                                <li>Format: JPG, PNG, atau PDF</li>
                                <li>Ukuran maksimal: 5MB</li>
                                <li>Gambar jelas dan tidak blur</li>
                            </ul>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label">Pilih File Bukti Transfer</label>
                                <input type="file" name="bukti" class="form-control" 
                                       accept="image/*,.pdf" required>
                                <small style="color: var(--gray);">Upload screenshot dari mobile banking Anda</small>
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
                            <span>Total Transfer:</span>
                            <span><?= rupiah($pesanan['total_harga']) ?></span>
                        </div>
                        
                        <div style="margin-top: 1rem; padding: 1rem; background: #e0f2fe; border-radius: 8px; font-size: 0.85rem; text-align: center;">
                            <strong>Transfer tepat sesuai nominal di atas</strong>
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