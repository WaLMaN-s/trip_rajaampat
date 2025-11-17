<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

// Get all gallery photos
$stmt = $pdo->query("SELECT * FROM galeri ORDER BY urutan ASC, created_at DESC");
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin <?= SITE_NAME ?></title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 class="section-title" style="margin-bottom: 0;">📸 Kelola Galeri Foto</h2>
                <a href="galeri-add.php" class="btn btn-primary">+ Tambah Foto</a>
            </div>
            
            <?php show_alert(); ?>
            
            <?php if (empty($photos)): ?>
                <div class="detail-info" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📸</div>
                    <h3 style="color: var(--gray); margin-bottom: 1rem;">Belum ada foto di galeri</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Mulai tambahkan foto pertama Anda!</p>
                    <a href="galeri-add.php" class="btn btn-primary">+ Tambah Foto</a>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($photos as $foto): ?>
                    <div class="card" style="position: relative;">
                        <img src="../public/uploads/galeri/<?= htmlspecialchars($foto['foto']) ?>" 
                             alt="<?= htmlspecialchars($foto['judul']) ?>" 
                             class="card-img"
                             onerror="this.src='../public/assets/img/placeholder.jpg'">
                        
                        <?php if (!$foto['tampilkan']): ?>
                            <div style="position: absolute; top: 10px; left: 10px; background: rgba(239,68,68,0.9); color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600;">
                                Disembunyikan
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($foto['judul']) ?></h3>
                            <?php if ($foto['deskripsi']): ?>
                                <p class="card-text"><?= htmlspecialchars(substr($foto['deskripsi'], 0, 80)) ?>...</p>
                            <?php endif; ?>
                            
                            <div style="margin: 1rem 0; padding: 0.75rem; background: #f8fafc; border-radius: 8px; font-size: 0.9rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: var(--gray);">Urutan:</span>
                                    <strong>#<?= $foto['urutan'] ?></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--gray);">Status:</span>
                                    <?php if ($foto['tampilkan']): ?>
                                        <span class="badge badge-valid">✓ Tampil</span>
                                    <?php else: ?>
                                        <span class="badge badge-invalid">✗ Disembunyikan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <a href="galeri-edit.php?id=<?= $foto['id'] ?>" 
                                   class="btn" 
                                   style="background: var(--secondary); color: white; padding: 0.75rem; font-size: 0.9rem; text-align: center;">
                                    ✏️ Edit
                                </a>
                                <a href="galeri-delete.php?id=<?= $foto['id'] ?>" 
                                   class="btn" 
                                   style="background: var(--danger); color: white; padding: 0.75rem; font-size: 0.9rem; text-align: center;"
                                   onclick="return confirm('Yakin ingin menghapus foto ini?')">
                                    🗑️ Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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