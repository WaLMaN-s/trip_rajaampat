<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

require_admin();

// Get all packages
$stmt = $pdo->query("SELECT * FROM paket_wisata ORDER BY created_at DESC");
$packages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket Wisata - Admin <?= SITE_NAME ?></title>
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
                <h2 class="section-title" style="margin-bottom: 0;">📦 Kelola Paket Wisata</h2>
                <a href="paket-add.php" class="btn btn-primary">+ Tambah Paket Baru</a>
            </div>
            
            <?php show_alert(); ?>
            
            <?php if (empty($packages)): ?>
                <div class="detail-info" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
                    <h3 style="color: var(--gray); margin-bottom: 1rem;">Belum ada paket wisata</h3>
                    <p style="color: var(--gray); margin-bottom: 2rem;">Mulai tambahkan paket wisata pertama Anda!</p>
                    <a href="paket-add.php" class="btn btn-primary">+ Tambah Paket</a>
                </div>
            <?php else: ?>
                <div class="card-grid">
                    <?php foreach ($packages as $package): ?>
                    <div class="card">
                        <img src="../public/uploads/paket/<?= htmlspecialchars($package['foto']) ?>" 
                             alt="<?= htmlspecialchars($package['nama_paket']) ?>" 
                             class="card-img"
                             onerror="this.src='../public/assets/img/placeholder.jpg'">
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($package['nama_paket']) ?></h3>
                            <p class="card-text"><?= substr(htmlspecialchars($package['deskripsi']), 0, 100) ?>...</p>
                            
                            <div class="card-meta">
                                <span class="card-price"><?= rupiah($package['harga']) ?></span>
                                <span class="card-duration">⏱️ <?= htmlspecialchars($package['durasi']) ?></span>
                            </div>
                            
                            <div style="margin-top: 1rem;">
                                <?php if ($package['tersedia']): ?>
                                    <span class="badge badge-valid">✓ Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-invalid">✗ Tidak Tersedia</span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 1rem;">
                                <a href="paket-edit.php?id=<?= $package['id'] ?>" 
                                   class="btn" 
                                   style="background: var(--secondary); color: white; padding: 0.75rem; font-size: 0.9rem;">
                                    ✏️ Edit
                                </a>
                                <a href="paket-delete.php?id=<?= $package['id'] ?>" 
                                   class="btn" 
                                   style="background: var(--danger); color: white; padding: 0.75rem; font-size: 0.9rem;"
                                   onclick="return confirm('Yakin ingin menghapus paket ini?')">
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