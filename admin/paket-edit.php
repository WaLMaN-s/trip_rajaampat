<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

$id = $_GET['id'] ?? 0;

// Get package
$stmt = $pdo->prepare("SELECT * FROM paket_wisata WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    alert('Paket tidak ditemukan!', 'danger');
    redirect('paket-list.php');
}

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
        $foto_name = $package['foto'];
        
        // Handle file upload if new file is uploaded
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['foto']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = 'Format foto tidak diizinkan! Gunakan JPG atau PNG.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 5MB!';
            } else {
                // Delete old photo
                if ($package['foto'] && file_exists(UPLOAD_DIR . 'paket/' . $package['foto'])) {
                    unlink(UPLOAD_DIR . 'paket/' . $package['foto']);
                }
                
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
                UPDATE paket_wisata 
                SET nama_paket = ?, deskripsi = ?, durasi = ?, harga = ?, 
                    foto = ?, fasilitas = ?, itinerary = ?, tersedia = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$nama, $deskripsi, $durasi, $harga, $foto_name, $fasilitas, $itinerary, $tersedia, $id])) {
                alert('Paket wisata berhasil diupdate!', 'success');
                redirect('paket-list.php');
            } else {
                $error = 'Gagal mengupdate paket wisata.';
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
    <title>Edit Paket Wisata - Admin <?= SITE_NAME ?></title>
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
            
            <h2 class="section-title">✏️ Edit Paket Wisata</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-info">
                <?php if ($package['foto']): ?>
                    <div style="margin-bottom: 2rem; text-align: center;">
                        <label class="form-label">Foto Saat Ini:</label>
                        <img src="../public/uploads/paket/<?= htmlspecialchars($package['foto']) ?>" 
                             alt="Current Photo"
                             style="max-width: 400px; width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
                             onerror="this.src='../public/assets/img/placeholder.jpg'">
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Nama Paket Wisata *</label>
                        <input type="text" name="nama_paket" class="form-control" 
                               placeholder="Contoh: Paket Snorkeling Paradise" required
                               value="<?= htmlspecialchars($_POST['nama_paket'] ?? $package['nama_paket']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi *</label>
                        <textarea name="deskripsi" class="form-control" required
                                  placeholder="Jelaskan tentang paket wisata ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? $package['deskripsi']) ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Durasi *</label>
                            <input type="text" name="durasi" class="form-control" 
                                   placeholder="Contoh: 3 Hari 2 Malam" required
                                   value="<?= htmlspecialchars($_POST['durasi'] ?? $package['durasi']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Harga (Rp) *</label>
                            <input type="number" name="harga" class="form-control" 
                                   placeholder="Contoh: 3500000" required min="0"
                                   value="<?= htmlspecialchars($_POST['harga'] ?? $package['harga']) ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ganti Foto Paket (Opsional)</label>
                        <input type="file" name="foto" class="form-control" 
                               accept="image/jpeg,image/png">
                        <small style="color: var(--gray);">Kosongkan jika tidak ingin mengganti foto. Format: JPG/PNG, Max: 5MB</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Fasilitas</label>
                        <textarea name="fasilitas" class="form-control"
                                  placeholder="Pisahkan dengan koma. Contoh: Penginapan, Makan 3x sehari, Peralatan snorkeling"><?= htmlspecialchars($_POST['fasilitas'] ?? $package['fasilitas']) ?></textarea>
                        <small style="color: var(--gray);">Pisahkan setiap fasilitas dengan koma (,)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Itinerary</label>
                        <textarea name="itinerary" class="form-control"
                                  placeholder="Pisahkan dengan | (pipe). Contoh: Hari 1: Check in|Hari 2: Snorkeling"><?= htmlspecialchars($_POST['itinerary'] ?? $package['itinerary']) ?></textarea>
                        <small style="color: var(--gray);">Pisahkan setiap hari dengan | (pipe/garis vertikal)</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="tersedia" value="1" 
                                   <?= $package['tersedia'] ? 'checked' : '' ?>
                                   style="width: 20px; height: 20px; cursor: pointer;">
                            <span class="form-label" style="margin: 0;">Paket tersedia untuk dijual</span>
                        </label>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem;">
                        <a href="paket-list.php" class="btn btn-outline" style="text-align: center;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            💾 Update Paket
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