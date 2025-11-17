<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

$id = $_GET['id'] ?? 0;

// Get photo
$stmt = $pdo->prepare("SELECT * FROM galeri WHERE id = ?");
$stmt->execute([$id]);
$foto = $stmt->fetch();

if (!$foto) {
    alert('Foto tidak ditemukan!', 'danger');
    redirect('galeri-list.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $urutan = (int)($_POST['urutan'] ?? 0);
    $tampilkan = isset($_POST['tampilkan']) ? 1 : 0;
    
    if (empty($judul)) {
        $error = 'Judul harus diisi!';
    } else {
        $foto_name = $foto['foto'];
        
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
                if ($foto['foto'] && file_exists(UPLOAD_DIR . 'galeri/' . $foto['foto'])) {
                    unlink(UPLOAD_DIR . 'galeri/' . $foto['foto']);
                }
                
                $foto_name = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
                $upload_path = UPLOAD_DIR . 'galeri/' . $foto_name;
                
                if (!is_dir(UPLOAD_DIR . 'galeri/')) {
                    mkdir(UPLOAD_DIR . 'galeri/', 0777, true);
                }
                
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $error = 'Gagal mengupload foto.';
                }
            }
        }
        
        if (empty($error)) {
            $stmt = $pdo->prepare("
                UPDATE galeri 
                SET judul = ?, deskripsi = ?, foto = ?, urutan = ?, tampilkan = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$judul, $deskripsi, $foto_name, $urutan, $tampilkan, $id])) {
                alert('Foto berhasil diupdate!', 'success');
                redirect('galeri-list.php');
            } else {
                $error = 'Gagal mengupdate foto.';
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
    <title>Edit Foto Galeri - Admin <?= SITE_NAME ?></title>
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
                <li><a href="pembayaran-list.php">Pembayaran</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <a href="galeri-list.php" style="color: var(--primary); margin-bottom: 1rem; display: inline-block;">← Kembali ke Galeri</a>
            
            <h2 class="section-title">✏️ Edit Foto Galeri</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <div class="detail-info">
                <?php if ($foto['foto']): ?>
                    <div style="margin-bottom: 2rem; text-align: center;">
                        <label class="form-label">Foto Saat Ini:</label>
                        <img src="../public/uploads/galeri/<?= htmlspecialchars($foto['foto']) ?>" 
                             alt="Current Photo"
                             style="max-width: 600px; width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"
                             onerror="this.src='../public/assets/img/placeholder.jpg'">
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Judul Foto *</label>
                        <input type="text" name="judul" class="form-control" 
                               placeholder="Contoh: Keindahan Bawah Laut Raja Ampat" required
                               value="<?= htmlspecialchars($_POST['judul'] ?? $foto['judul']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control"
                                  placeholder="Ceritakan tentang foto ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? $foto['deskripsi']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ganti Foto (Opsional)</label>
                        <input type="file" name="foto" class="form-control" 
                               accept="image/jpeg,image/png"
                               onchange="previewImage(this)">
                        <small style="color: var(--gray);">Kosongkan jika tidak ingin mengganti foto. Format: JPG/PNG, Max: 5MB</small>
                        
                        <div id="preview" style="margin-top: 1rem; display: none;">
                            <img id="preview-img" style="max-width: 400px; width: 100%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="urutan" class="form-control" 
                               placeholder="0" min="0"
                               value="<?= htmlspecialchars($_POST['urutan'] ?? $foto['urutan']) ?>">
                        <small style="color: var(--gray);">Semakin kecil angka, semakin depan posisinya</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="tampilkan" value="1" 
                                   <?= $foto['tampilkan'] ? 'checked' : '' ?>
                                   style="width: 20px; height: 20px; cursor: pointer;">
                            <span class="form-label" style="margin: 0;">Tampilkan di galeri</span>
                        </label>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem;">
                        <a href="galeri-list.php" class="btn btn-outline" style="text-align: center;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            💾 Update Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('preview');
        const previewImg = document.getElementById('preview-img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?> - Admin Panel</p>
            </div>
        </div>
    </footer>
</body>
</html>