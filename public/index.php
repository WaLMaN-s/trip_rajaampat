<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

// Get all available packages
$stmt = $pdo->query("SELECT * FROM paket_wisata WHERE tersedia = 1 ORDER BY created_at DESC");
$packages = $stmt->fetchAll();

// Get gallery photos
$stmt = $pdo->query("SELECT * FROM galeri WHERE tampilkan = 1 ORDER BY urutan ASC, created_at DESC LIMIT 12");
$gallery = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Jelajahi Surga Tersembunyi Indonesia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand"><?= SITE_NAME ?></a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="#paket">Paket Wisata</a></li>
                <li><a href="#galeri">Galeri</a></li>
                <li><a href="#syarat">S&K</a></li>
                <?php if (is_logged_in()): ?>
                    <?php if (is_admin()): ?>
                        <li><a href="../admin/dashboard.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="pyment-status.php">Pesanan Saya</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout </a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>🌴 Selamat Datang di Raja Ampat 🌊</h1>
            <p>Jelajahi keindahan surga bawah laut terbaik di dunia dengan paket wisata terpercaya dan berpengalaman</p>
            <a href="#paket" class="btn btn-primary">Lihat Paket Wisata</a>
        </div>
    </section>

    <!-- Packages Section -->
    <section class="section" id="paket">
        <div class="container">
            <h2 class="section-title">Paket Wisata Pilihan</h2>
            <?php show_alert(); ?>
            
            <div class="card-grid">
                <?php foreach ($packages as $package): ?>
                <div class="card">
                    <img src="uploads/paket/<?= htmlspecialchars($package['foto']) ?>" 
                         alt="<?= htmlspecialchars($package['nama_paket']) ?>" 
                         class="card-img"
                         onerror="this.src='assets/img/placeholder.jpg'">
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($package['nama_paket']) ?></h3>
                        <p class="card-text"><?= substr(htmlspecialchars($package['deskripsi']), 0, 100) ?>...</p>
                        <div class="card-meta">
                            <span class="card-price"><?= rupiah($package['harga']) ?></span>
                            <span class="card-duration">⏱️ <?= htmlspecialchars($package['durasi']) ?></span>
                        </div>
                        <a href="paket.php?id=<?= $package['id'] ?>" class="btn btn-primary" style="width: 100%;">Lihat Detail</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($packages)): ?>
                <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada paket wisata tersedia.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="section" style="background: white;" id="galeri">
        <div class="container">
            <h2 class="section-title">📸 Galeri Foto</h2>
            <p style="text-align: center; color: var(--gray); max-width: 600px; margin: 0 auto 3rem;">
                Lihat keindahan Raja Ampat melalui koleksi foto-foto menakjubkan kami
            </p>
            
            <?php if (empty($gallery)): ?>
                <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada foto di galeri.</p>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($gallery as $foto): ?>
                    <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($foto['foto']) ?>', '<?= htmlspecialchars($foto['judul']) ?>', '<?= htmlspecialchars($foto['deskripsi'] ?? '') ?>')">
                        <img src="uploads/galeri/<?= htmlspecialchars($foto['foto']) ?>" 
                             alt="<?= htmlspecialchars($foto['judul']) ?>"
                             onerror="this.src='assets/img/placeholder.jpg'">
                        <div class="gallery-overlay">
                            <div class="gallery-info">
                                <h4><?= htmlspecialchars($foto['judul']) ?></h4>
                                <?php if ($foto['deskripsi']): ?>
                                    <p><?= htmlspecialchars(substr($foto['deskripsi'], 0, 60)) ?>...</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-img">
        <div class="lightbox-caption">
            <h3 id="lightbox-title"></h3>
            <p id="lightbox-desc"></p>
        </div>
    </div>

    <script>
    function openLightbox(foto, judul, deskripsi) {
        document.getElementById('lightbox').style.display = 'flex';
        document.getElementById('lightbox-img').src = 'uploads/galeri/' + foto;
        document.getElementById('lightbox-title').textContent = judul;
        document.getElementById('lightbox-desc').textContent = deskripsi;
        document.body.style.overflow = 'hidden';
    }
    
    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
    </script>

    <!-- Syarat & Ketentuan Section -->
    <section class="section" id="syarat" style="background: linear-gradient(135deg, rgba(157, 188, 235, 1) 0%, #a0e1f3ff 100%);">
        <div class="container">
            <h2 class="section-title" style="color: white !important;">📋 Syarat & Ketentuan</h2>
            <p style="text-align: center; #a0e1f3: ; max-width: 600px; margin: 0 auto 3rem; opacity: 0.95;">
                Harap baca syarat dan ketentuan berikut sebelum melakukan pemesanan
            </p>
            
            <div class="card-grid">
                <!-- Cuaca & Force Majeure -->
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: center; font-size: 3rem; margin-bottom: 1rem;">🌦️</div>
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">Cuaca & Force Majeure</h3>
                        <ul style="list-style: none; padding: 0; line-height: 2;">
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                ⚠️ Trip dapat ditunda/dibatalkan jika cuaca buruk, kapal tidak diizinkan beroperasi, atau terjadi force majeure
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                💰 Penyelenggara tidak bertanggung jawab atas biaya tambahan di luar paket (hotel tambahan, tiket, dll)
                            </li>
                            <li style="padding: 0.5rem 0;">
                                ♻️ Refund menyesuaikan kebijakan internal & kondisi
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tanggung Jawab Peserta -->
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: center; font-size: 3rem; margin-bottom: 1rem;">👤</div>
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">Tanggung Jawab Peserta</h3>
                        <ul style="list-style: none; padding: 0; line-height: 2;">
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                ✅ Wajib mengikuti instruksi guide demi keselamatan
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                🎒 Peserta bertanggung jawab atas barang pribadi masing-masing
                            </li>
                            <li style="padding: 0.5rem 0;">
                                💸 Kerusakan fasilitas akibat kelalaian peserta akan dikenakan biaya penggantian
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Kontak & Informasi -->
                <div class="card">
                    <div class="card-body">
                        <div style="text-align: center; font-size: 3rem; margin-bottom: 1rem;">📞</div>
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">Kontak & Informasi</h3>
                        <ul style="list-style: none; padding: 0; line-height: 2;">
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                                📧 Semua pemesanan dan pertanyaan dilakukan melalui kontak resmi penyelenggara
                            </li>
                            <li style="padding: 0.5rem 0;">
                                📖 Harap membaca detail paket masing-masing sebelum melakukan DP (Down Payment)
                            </li>
                        </ul>
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #e0f2fe; border-radius: 8px; text-align: center;">
                            <strong style="color: var(--primary);">Ada Pertanyaan?</strong><br>
                            <a href="https://wa.me/6281234567890" 
                               target="_blank"
                               class="btn btn-primary" 
                               style="margin-top: 0.5rem; display: inline-block;">
                                💬 Chat WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
<div style="
    margin-top: 3rem; 
    padding: 2rem; 
    background: rgba(255,255,255,0.25); 
    border-radius: 12px; 
    backdrop-filter: blur(10px);
    color: #000;
">
    <h4 style="color: #0033cc; margin-bottom: 2rem; text-align: center;">⚡ Catatan Penting</h4>
    <p style="margin: 0.5rem 0 0 0; font-size: 1rem; color: #222;">
        Pembatalan oleh peserta dikenakan biaya admin sesuai kebijakan. Hubungi kami untuk detail.
    </p>
</div>

<div style="
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
    gap: 1.5rem; 
    margin-top: 1.5rem;
">
    <div style="
        padding: 1rem; 
        background: #ffffffd9; 
        border-radius: 10px;
        color: #000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    ">
        <strong>✈️ Transportasi</strong>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">
            Tiket pesawat & transportasi lokal ke titik kumpul tidak termasuk dalam paket kecuali disebutkan.
        </p>
    </div>

    <div style="
        padding: 1rem; 
        background: #ffffffd9; 
        border-radius: 10px;
        color: #000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    ">
        <strong>💳 Pembayaran</strong>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">
            Pembayaran bisa QRIS atau transfer ke bank BCA.
        </p>
    </div>

    <div style="
        padding: 1rem; 
        background: #ffffffd9; 
        border-radius: 10px;
        color: #000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    ">
        <strong>🎫 Pembatalan</strong>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">
            Silakan menghubungi admin untuk info lebih detail mengenai pembatalan.
        </p>
    </div>
</div>

           

            <!-- Agreement Statement -->
            <div style="margin-top: 2rem; padding: 1.5rem; background: #fef3c7; border-radius: 8px; text-align: center; border-left: 4px solid var(--secondary);">
                <strong style="color: var(--dark); font-size: 1.1rem;">
                    ✓ Dengan melakukan pemesanan, Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat & ketentuan yang berlaku
                </strong>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Raja Ampat Trip</h3>
                    <p>Penyedia layanan wisata terpercaya di Raja Ampat sejak tadi malam. Kami berkomitmen memberikan pengalaman terbaik untuk perjalanan Anda.</p>
                </div>
                <div class="footer-section">
                    <h3>Kontak</h3>
                    <p>📧 Email: info@404brainnotfound</p>
                    <p>📱 WhatsApp: +62 812-3456-7890</p>
                    <p>📍 Waisai, Raja Ampat, Papua Barat Daya</p>
                </div>
                <div class="footer-section">
                    <h3>Sosial Media</h3>
                    <p>Instagram: @404brainnotfound</p>
                    <p>Facebook: @404brainotfaound</p>
                    <p>YouTube: Raja Ampat Adventures</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>