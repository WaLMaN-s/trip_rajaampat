<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_login();

$paket_id = $_GET['id'] ?? 0;

// Get package
$stmt = $pdo->prepare("SELECT * FROM paket_wisata WHERE id = ? AND tersedia = 1");
$stmt->execute([$paket_id]);
$package = $stmt->fetch();

if (!$package) {
    redirect('index.php');
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([get_user_id()]);
$user = $stmt->fetch();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal_berangkat'] ?? '';
    $jumlah = (int)($_POST['jumlah_peserta'] ?? 1);
    $nama = $_POST['nama_pemesan'] ?? '';
    $email = $_POST['email_pemesan'] ?? '';
    $no_hp = $_POST['no_hp_pemesan'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    if (empty($tanggal) || $jumlah < 1 || empty($nama) || empty($email) || empty($no_hp)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $total = $package['harga'] * $jumlah;
        
        $stmt = $pdo->prepare("
            INSERT INTO pesanan (user_id, paket_id, tanggal_berangkat, jumlah_peserta, total_harga, 
                                nama_pemesan, email_pemesan, no_hp_pemesan, catatan, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        if ($stmt->execute([get_user_id(), $paket_id, $tanggal, $jumlah, $total, $nama, $email, $no_hp, $catatan])) {
            $pesanan_id = $pdo->lastInsertId();
            redirect("payment.php?id=$pesanan_id");
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= SITE_NAME ?></title>
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
            <a href="paket.php?id=<?= $package['id'] ?>" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali</a>
            
            <h2 class="section-title">Checkout Pemesanan</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-container">
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">📋 Form Pemesanan</h3>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">Tanggal Keberangkatan</label>
                                <input type="date" name="tanggal_berangkat" class="form-control" 
                                       min="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                                <small style="color: var(--gray);">Minimal 3 hari dari sekarang</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Jumlah Peserta</label>
                                <input type="number" name="jumlah_peserta" class="form-control" 
                                       min="1" max="20" value="1" required id="jumlah"
                                       onchange="hitungTotal()">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nama Pemesan</label>
                                <input type="text" name="nama_pemesan" class="form-control" 
                                       value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email_pemesan" class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">No. HP (WhatsApp)</label>
                                <input type="tel" name="no_hp_pemesan" class="form-control" 
                                       value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" 
                                       placeholder="08xxxxxxxxxx" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea name="catatan" class="form-control" 
                                          placeholder="Permintaan khusus, alergi makanan, dll"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                Lanjut ke Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
                
                <div>
                    <div class="detail-info">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">📦 Detail Paket</h3>
                        <h4><?= htmlspecialchars($package['nama_paket']) ?></h4>
                        <p style="color: var(--gray); margin: 0.5rem 0;"><?= htmlspecialchars($package['durasi']) ?></p>
                        
                        <div style="margin: 2rem 0; padding: 1.5rem; background: #f8fafc; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Harga per orang:</span>
                                <span id="harga"><?= rupiah($package['harga']) ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Jumlah peserta:</span>
                                <span id="peserta">1</span>
                            </div>
                            <hr style="margin: 1rem 0; border: none; border-top: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; color: var(--primary);">
                                <span>Total:</span>
                                <span id="total"><?= rupiah($package['harga']) ?></span>
                            </div>
                        </div>
                        
                        <div style="padding: 1rem; background: #fef3c7; border-radius: 8px; border-left: 4px solid var(--secondary);">
                            <strong>ℹ️ Informasi:</strong>
                            <ul style="margin: 0.5rem 0 0 1.5rem; font-size: 0.9rem;">
                                <li>Harga sudah termasuk fasilitas yang tertera</li>
                                <li>Pembayaran dapat melalui QRIS atau Transfer Bank</li>
                                <li>Konfirmasi maksimal 1x24 jam</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    const hargaPerOrang = <?= $package['harga'] ?>;
    
    function hitungTotal() {
        const jumlah = parseInt(document.getElementById('jumlah').value) || 1;
        const total = hargaPerOrang * jumlah;
        
        document.getElementById('peserta').textContent = jumlah;
        document.getElementById('total').textContent = formatRupiah(total);
    }
    
    function formatRupiah(angka) {
        return 'Rp ' + angka.toLocaleString('id-ID');
    }
    </script>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>