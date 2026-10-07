# Raja Ampat Trip

Website pemesanan paket wisata Raja Ampat: pengunjung bisa lihat paket wisata & galeri foto,
daftar/login, checkout paket, upload bukti pembayaran (QRIS/Transfer Bank), lalu memantau
status pesanannya. Admin punya panel terpisah untuk kelola paket, galeri, verifikasi
pembayaran, dan data user.

Dibangun dengan PHP native (tanpa framework) + MySQL, didesain untuk jalan di **XAMPP**.

## Fitur

**Pengunjung**
- Lihat daftar paket wisata & galeri foto di beranda
- Detail paket (fasilitas, itinerary, harga)
- Register & login
- Checkout paket (tanggal berangkat, jumlah peserta, catatan)
- Bayar via QRIS atau Transfer Bank + upload bukti pembayaran
- Pantau status pesanan (belum bayar / menunggu verifikasi / valid / ditolak)
- Batalkan pesanan yang belum lunas

**Admin**
- Dashboard statistik (total paket, pesanan, pendapatan, user, dll)
- CRUD paket wisata & galeri foto
- Verifikasi / tolak bukti pembayaran
- Daftar & detail user + riwayat pesanannya
- Log pesanan yang dibatalkan

## Cara Menjalankan dengan XAMPP

1. **Install XAMPP** (jika belum ada) dari https://www.apachefriends.org, lalu install seperti biasa.

2. **Salin project ke folder `htdocs`** XAMPP.
   - Windows: `C:\xampp\htdocs\trip_rajaampat`
   - Linux: `/opt/lampp/htdocs/trip_rajaampat`

   Pastikan strukturnya jadi `htdocs/trip_rajaampat/public/index.php`, dst — jangan naruh isi
   project langsung di root `htdocs`.

3. **Nyalakan Apache & MySQL** dari XAMPP Control Panel (di Linux/LAMPP: `sudo /opt/lampp/lampp start`).

4. **Buat database**, lewat phpMyAdmin (`http://localhost/phpmyadmin`) atau terminal:
   ```bash
   mysql -u root -e "CREATE DATABASE rajaampat_trip"
   mysql -u root rajaampat_trip < sql/init_db.sql
   ```
   `sql/init_db.sql` otomatis membuat semua tabel + 1 akun admin + beberapa paket/galeri contoh.

5. **Cek koneksi database** di `includes/config.php` kalau setup MySQL kamu beda dari default
   XAMPP (default: host `localhost`, user `root`, password kosong):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'rajaampat_trip');
   ```

6. **Buka di browser:**
   ```
   http://localhost/trip_rajaampat/public/
   ```

## Login / Akun Demo

Login pengunjung dan admin memakai satu halaman yang sama (`public/login.php`) — sistem
otomatis mengarahkan sesuai role setelah login.

| Role  | Cara masuk | Email | Password |
|-------|-----------|-------|----------|
| Admin | `http://localhost/trip_rajaampat/public/login.php` | `admin@rajaampat.com` | `password` |
| User  | Daftar dulu lewat `.../public/register.php`, lalu login dengan akun yang baru dibuat | — | — |

⚠️ Akun admin di atas berasal dari data contoh (`sql/init_db.sql`). Kalau website ini dipakai
sungguhan (bukan sekadar demo lokal), **ganti password admin lewat database** sebelum
di-deploy publik — belum ada halaman ganti password di panel admin.

Setelah login sebagai admin, panel admin bisa diakses di:
```
http://localhost/trip_rajaampat/admin/dashboard.php
```

## Troubleshooting

**Upload foto/bukti pembayaran gagal terus ("Gagal mengupload foto")** — hampir selalu
karena folder `public/uploads/` (dan subfolder `galeri/`, `paket/`, `pembayaran/`) tidak
bisa ditulis oleh user yang menjalankan Apache/PHP (biasanya `daemon`, `www-data`, atau
`nobody`, bukan user login kamu). Kalau project ini di-copy/clone dengan kepemilikan
folder milik user kamu sendiri, izin default (755) memang tidak mengizinkan user lain
menulis ke situ. Perbaikannya:
```bash
chmod -R 777 public/uploads      # cukup untuk dev lokal
```
Pesan error sekarang juga menampilkan alasan asli dari PHP (mis. "Permission denied")
supaya lebih mudah mengenali masalah ini tanpa harus menebak-nebak.
