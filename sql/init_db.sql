-- Database: rajaampat_trip
CREATE DATABASE IF NOT EXISTS rajaampat_trip;
USE rajaampat_trip;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: paket_wisata
CREATE TABLE IF NOT EXISTS paket_wisata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    durasi VARCHAR(50),
    harga DECIMAL(12,2) NOT NULL,
    foto VARCHAR(255),
    fasilitas TEXT,
    itinerary TEXT,
    tersedia BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paket_id INT NOT NULL,
    tanggal_berangkat DATE NOT NULL,
    jumlah_peserta INT NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    nama_pemesan VARCHAR(100) NOT NULL,
    email_pemesan VARCHAR(100) NOT NULL,
    no_hp_pemesan VARCHAR(20) NOT NULL,
    catatan TEXT,
    status ENUM('pending', 'paid', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (paket_id) REFERENCES paket_wisata(id) ON DELETE CASCADE
);

-- Table: pembayaran
CREATE TABLE IF NOT EXISTS pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    metode_pembayaran ENUM('qris', 'transfer') NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    bukti_pembayaran VARCHAR(255),
    status ENUM('pending', 'valid', 'invalid') DEFAULT 'pending',
    keterangan TEXT,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_verifikasi TIMESTAMP NULL,
    verified_by INT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin
INSERT INTO users (nama, email, password, role) VALUES 
('Admin', 'admin@rajaampat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password

-- Table: galeri
CREATE TABLE IF NOT EXISTS galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    foto VARCHAR(255) NOT NULL,
    urutan INT DEFAULT 0,
    tampilkan BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: cancelled_orders_log (untuk tracking pesanan yang dibatalkan)
CREATE TABLE IF NOT EXISTS cancelled_orders_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    user_id INT NOT NULL,
    user_nama VARCHAR(100),
    user_email VARCHAR(100),
    paket_id INT,
    nama_paket VARCHAR(200),
    tanggal_berangkat DATE,
    jumlah_peserta INT,
    total_harga DECIMAL(12,2),
    alasan_pembatalan TEXT,
    cancelled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample paket wisata
INSERT INTO paket_wisata (nama_paket, deskripsi, durasi, harga, foto, fasilitas, itinerary, tersedia) VALUES
('Paket ONE DAY TRIP', 'Nikmati keindahan bawah laut Raja Ampat dengan snorkeling di spot-spot terbaik', '1 Hari', 1950000, 'snorkeling.jpg', 'Speedboat, Makan 3 kali, Biaya masuk tiap spot, Dokumentasi underwater', 'Piaynemo - Friwen - Yenbuba - Pasir Timbul', 1),
('Paket 2 DAYS 1 NIGHT', 'Jelajahi pulau-pulau eksotis di Raja Ampat dengan boat premium', '2 Hari 1 Malam', 3950000, 'island.jpg', 'Penginapan resort, Makan 6x, Speedboat premium, Guide & crew, Dokumentasi profesional, Welcome drink', 'Hari 1: Arrival - Pianemo - Telaga Bintang|Hari 2: Friwen - Pasir Timbul - Kali Biru - Departure', 1),
('Paket 3 DAYS 2 NIGHTS', 'Paket lengkap menjelajahi spot-spot terbaik Raja Ampat', '3 Hari 2 Malam', 5950000, 'diving.jpg', 'Penginapan homestay, Makan 9x, Speedboat, Guide profesional, Dokumentasi, Snorkeling equipment', 'Hari 1: Piaynemo - Telaga Bintang - Friwen|Hari 2: Yenbuba - Pasir Timbul - Kali Biru|Hari 3: Kabui - Batu Pensil - Sayundarak - Departure', 1),
('Paket Honeymoon Romantic', 'Paket spesial untuk pasangan yang ingin merayakan momen istimewa di surga tersembunyi', '3 Hari 2 Malam', 7500000, 'honeymoon.jpg', 'Private cottage, Romantic dinner, Couple spa, Private boat tour, Flower decoration, Fotografi profesional, Airport transfer', 'Hari 1: Welcome - Romantic dinner di pantai|Hari 2: Private island tour - Piaynemo - Telaga Bintang|Hari 3: Sunrise breakfast - Departure', 1);

-- Insert sample galeri
INSERT INTO galeri (judul, deskripsi, foto, urutan, tampilkan) VALUES
('Piaynemo', 'Piaynemo adalah ikon Raja Ampat dengan pemandangan gugusan pulau karst yang indah', 'gallery-1.jpg', 1, 1),
('Telaga Bintang', 'Telaga Bintang terkenal karena bentuknya yang menyerupai bintang jika dilihat dari atas', 'gallery-2.jpg', 2, 1),
('Friwen', 'Friwen terkenal dengan air laut super jernih dan suasana pantai yang tenang', 'gallery-3.jpg', 3, 1),
('Yenbuba', 'Yenbuba memiliki jembatan panjang yang menjadi spot foto favorit', 'gallery-4.jpg', 4, 1),
('Pasir Timbul', 'Pasir Timbul adalah daratan pasir putih yang muncul saat air laut surut', 'gallery-5.jpg', 5, 1),
('Kali Biru', 'Kali Biru terkenal dengan warna airnya yang biru jernih', 'gallery-6.jpg', 6, 1),
('Kabui', 'Kabui memiliki tebing karst tinggi dengan air toska yang indah', 'gallery-7.jpg', 7, 1),
('Batu Pensil', 'Batu Pensil adalah batu karst tinggi yang berbentuk mirip pensil', 'gallery-8.jpg', 8, 1),
('Sayundarak', 'Sayundarak menawarkan pemandangan karst megah dari atas perahu', 'gallery-9.jpg', 9, 1),
('Diving Paradise', 'Spot diving terbaik dengan visibility tinggi dan keanekaragaman hayati luar biasa', 'gallery-10.jpg', 10, 1);