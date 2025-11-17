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

-- Insert sample paket wisata
INSERT INTO paket_wisata (nama_paket, deskripsi, durasi, harga, foto, fasilitas, itinerary, tersedia) VALUES
('Paket Snorkeling Paradise', 'Nikmati keindahan bawah laut Raja Ampat dengan snorkeling di spot-spot terbaik', '3 Hari 2 Malam', 3500000, 'snorkeling.jpg', 'Penginapan, Makan 3x sehari, Peralatan snorkeling, Guide profesional, Dokumentasi underwater', 'Hari 1: Penjemputan - Check in - Island hopping|Hari 2: Snorkeling spot terbaik - Manta point|Hari 3: Sunrise tour - Check out', 1),
('Paket Island Hopping Premium', 'Jelajahi pulau-pulau eksotis di Raja Ampat dengan boat premium', '4 Hari 3 Malam', 5500000, 'island.jpg', 'Penginapan resort, Makan 3x sehari, Speedboat premium, Guide & crew, Dokumentasi profesional, Welcome drink', 'Hari 1: Arrival - Wayag viewpoint|Hari 2: Pianemo - Lagoon biru|Hari 3: Pasir timbul - Snorkeling|Hari 4: Breakfast - Departure', 1),
('Paket Diving Adventure', 'Eksplorasi diving spot kelas dunia di Raja Ampat untuk para penyelam', '5 Hari 4 Malam', 8500000, 'diving.jpg', 'Penginapan dive resort, Makan 3x sehari, 8x diving, Diving equipment, Sertifikat diver, Insurance, Guide & DM', 'Hari 1: Arrival - Check dive|Hari 2-4: Diving sessions di berbagai spot|Hari 5: Relax dive - Departure', 1),
('Paket Honeymoon Romantic', 'Paket spesial untuk pasangan yang ingin merayakan momen istimewa di surga tersembunyi', '3 Hari 2 Malam', 7500000, 'honeymoon.jpg', 'Private cottage, Romantic dinner, Couple spa, Private boat tour, Flower decoration, Fotografi profesional, Airport transfer', 'Hari 1: Welcome - Romantic dinner di pantai|Hari 2: Private island tour - Couple spa|Hari 3: Sunrise breakfast - Departure', 1);

-- Insert sample galeri
INSERT INTO galeri (judul, deskripsi, foto, urutan, tampilkan) VALUES
('Keindahan Bawah Laut Raja Ampat', 'Terumbu karang yang masih alami dengan keanekaragaman hayati luar biasa', 'gallery-1.jpg', 1, 1),
('Pianemo - Icon Raja Ampat', 'Pemandangan dari atas bukit Pianemo yang menakjubkan', 'gallery-2.jpg', 2, 1),
('Snorkeling dengan Manta', 'Pengalaman berenang bersama manta ray yang ramah', 'gallery-3.jpg', 3, 1),
('Sunset di Pantai Pasir Putih', 'Momen sunset yang indah di pantai Raja Ampat', 'gallery-4.jpg', 4, 1),
('Wayag Island', 'Kepulauan karst yang ikonik di Raja Ampat', 'gallery-5.jpg', 5, 1),
('Diving Paradise', 'Spot diving terbaik dengan visibility tinggi', 'gallery-6.jpg', 6, 1);