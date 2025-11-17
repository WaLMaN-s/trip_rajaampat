<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_paket'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $durasi = $_POST['durasi'] ?? '';
    $harga = $_POST['harga'] ?? 0;
    $fasilitas = $_POST['fasilitas'] ?? '';
    $itinerary = $_POST['itinerary'] ?? '';
    $tersedia = isset($_POST['tersedia']) ? 1 : 0;
    
    if (empty($nama) || empty($deskripsi) || empty($durasi) || $harga <= 0) {
        $error = 'Semua field wajib diisi!';
    } else {
        $foto_name = '';
        
        // Handle file upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['foto']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = 'Format foto tidak diizinkan! Gunakan JPG atau PNG.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 5MB!';
            } else {
                $foto_name = 'paket_' . time() . '.' . $ext;
                $upload_path = UPLOAD_DIR . 'paket/' . $foto_name;
                
                if (!is_dir(UPLOAD_DIR . 'paket/')) {
                    mkdir(UPLOAD_DIR . 'paket/', 0777, true);
                }
                
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $error = 'Gagal mengupload foto.';
                }
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("
                INSERT INTO paket_wisata (nama_paket, deskripsi, durasi, harga, foto, fasilitas, itinerary, tersedia)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$nama, $deskripsi, $durasi, $harga, $foto_name, $fasilitas, $itinerary, $tersedia])) {
                alert('Paket wisata berhasil ditambahkan!', 'success');
                redirect('paket-list.php');
            } else {
                $error = 'Gagal menyimpan paket wisata.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Paket Wisata - Admin <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">Admin - <?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="paket-list.php">Paket Wisata</a></li>
                <li><a href="pembayaran-list.php">Pembayaran</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <a href="paket-list.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Daftar Paket</a>
            
            <h2 class="section-title">➕ Tambah Paket Wisata Baru</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-info">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Nama Paket Wisata *</label>
                        <input type="text" name="nama_paket" class="form-control" 
                               placeholder="Contoh: Paket Snorkeling Paradise" required
                               value="<?= htmlspecialchars($_POST['nama_paket'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi *</label>
                        <textarea name="deskripsi" class="form-control" required
                                  placeholder="Jelaskan tentang paket wisata ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Durasi *</label>
                            <input type="text" name="durasi" class="form-control" 
                                   placeholder="Contoh: 3 Hari 2 Malam" required
                                   value="<?= htmlspecialchars($_POST['durasi'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Harga (Rp) *</label>
                            <input type="number" name="harga" class="form-control" 
                                   placeholder="Contoh: 3500000" required min="0"
                                   value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Foto Paket</label>
                        <input type="file" name="foto" class="form-control" 
                               accept="image/jpeg,image/png">
                        <small style="color: var(--gray);">Format: JPG/PNG, Max: 5MB, Rekomendasi: 800x600px</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Fasilitas</label>
                        <textarea name="fasilitas" class="form-control"
                                  placeholder="Pisahkan dengan koma. Contoh: Penginapan, Makan 3x sehari, Peralatan snorkeling, Guide profesional"><?= htmlspecialchars($_POST['fasilitas'] ?? '') ?></textarea>
                        <small style="color: var(--gray);">Pisahkan setiap fasilitas dengan koma (,)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Itinerary</label>
                        <textarea name="itinerary" class="form-control"
                                  placeholder="Pisahkan dengan | (pipe). Contoh: Hari 1: Check in - Island hopping|Hari 2: Snorkeling|Hari 3: Check out"><?= htmlspecialchars($_POST['itinerary'] ?? '') ?></textarea>
                        <small style="color: var(--gray);">Pisahkan setiap hari dengan | (pipe/garis vertikal)</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="tersedia" value="1" checked
                                   style="width: 20px; height: 20px; cursor: pointer;">
                            <span class="form-label" style="margin: 0;">Paket tersedia untuk dijual</span>
                        </label>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem;">
                        <a href="paket-list.php" class="btn btn-outline" style="text-align: center;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            💾 Simpan Paket
                        </button>
                    </div>
                </form>
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