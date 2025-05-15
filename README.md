<div align="center">
  <img src="https://private-user-images.githubusercontent.com/128197332/318203316-29788684-29e3-4d02-b3ad-4fe637ba3923.gif?jwt=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJnaXRodWIuY29tIiwiYXVkIjoicmF3LmdpdGh1YnVzZXJjb250ZW50LmNvbSIsImtleSI6ImtleTUiLCJleHAiOjE3NDcwMzEyMzEsIm5iZiI6MTc0NzAzMDkzMSwicGF0aCI6Ii8xMjgxOTczMzIvMzE4MjAzMzE2LTI5Nzg4Njg0LTI5ZTMtNGQwMi1iM2FkLTRmZTYzN2JhMzkyMy5naWY_WC1BbXotQWxnb3JpdGhtPUFXUzQtSE1BQy1TSEEyNTYmWC1BbXotQ3JlZGVudGlhbD1BS0lBVkNPRFlMU0E1M1BRSzRaQSUyRjIwMjUwNTEyJTJGdXMtZWFzdC0xJTJGczMlMkZhd3M0X3JlcXVlc3QmWC1BbXotRGF0ZT0yMDI1MDUxMlQwNjIyMTFaJlgtQW16LUV4cGlyZXM9MzAwJlgtQW16LVNpZ25hdHVyZT00YjViN2QyNjRkZWRjODY4ODI5MTQ5ZjZlNjgwZDAwYTJmNTBhOTQ0YjA0MGI3OWVjYjVhZmY1NjQ4YTk2NWUyJlgtQW16LVNpZ25lZEhlYWRlcnM9aG9zdCJ9.eWsGvmI9janBzin22nrJUQx4KBU9iFsUg-l8oR0o9uI" width="900" height="100"/>
  <h1>Sistem Informasi Perpustakaan MTSN 6 Garut</h1>
  <p>Aplikasi manajemen perpustakaan modern berbasis Laravel dengan sistem notifikasi email dan QR Code</p>
  
  <p>
    <a href="#fitur"><img src="https://img.shields.io/badge/Fitur-Lengkap-brightgreen" alt="Fitur"></a>
    <a href="#penggunaan"><img src="https://img.shields.io/badge/Status-Aktif-blue" alt="Status"></a>
    <a href="#instalasi"><img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel"></a>
    <a href="#lisensi"><img src="https://img.shields.io/badge/Lisensi-MIT-yellow" alt="License"></a>
    <a href="#qr-code"><img src="https://img.shields.io/badge/QR%20Code-Terintegrasi-orange" alt="QR Code"></a>
  </p>
</div>

## 📚 Tentang Aplikasi

Sistem Informasi Perpustakaan MTSN 6 Garut adalah aplikasi manajemen perpustakaan komprehensif yang dibuat untuk memudahkan pengelolaan koleksi buku, peminjaman, dan pelacakan koleksi perpustakaan. Aplikasi ini dirancang dengan fokus pada pengalaman pengguna yang intuitif dan dilengkapi dengan sistem notifikasi otomatis untuk pengembalian buku.

### ✨ Fitur Utama

- **Manajemen Buku**
  - Pencatatan lengkap data buku dengan gambar sampul
  - Kategorisasi buku multi-kategori
  - Pelacakan stok buku secara real-time
  - QR Code untuk peminjaman cepat dengan logo sekolah
  - Download QR Code untuk dicetak dan ditempelkan pada buku fisik
  
- **Sistem Peminjaman**
  - Peminjaman dan pengembalian dengan antarmuka intuitif
  - Peminjaman buku melalui scan QR Code
  - Batasan peminjaman berdasarkan tipe pengguna (siswa/guru/staff)
  - Pelacakan riwayat peminjaman dengan statistik lengkap
  - Deteksi otomatis pengembalian terlambat

- **Manajemen Pengguna**
  - Akun untuk admin, guru, staff, dan siswa
  - Profil pengguna yang dapat dikelola
  - Hak akses berbasis peran
  - Riwayat aktivitas peminjaman per pengguna

- **Notifikasi & Pengingat**
  - Sistem notifikasi email 24 jam berbasis Windows Service
  - Pengingat otomatis untuk batas pengembalian buku
  - Notifikasi terlambat untuk buku yang belum dikembalikan
  - Pengiriman email dengan antrian asinkron

- **Fitur Administrasi**
  - Dashboard statistik lengkap dengan grafik interaktif
  - Laporan peminjaman dan pengembalian real-time
  - Data buku paling populer dan paling sering dipinjam
  - Filter dan pencarian data multi-parameter

## 📊 Arsitektur Sistem

Sistem notifikasi perpustakaan menggunakan arsitektur berikut:

```
+-------------------+     +----------------+     +-----------------+
| Laravel Scheduler |---->| Database Queue |---->| Laravel Queue   |
| (Windows Service) |     | (MySQL/MariaDB)|     | Worker          |
+-------------------+     +----------------+     | (Windows Service)|
                                |                +-----------------+
                                |                        |
                                v                        v
                          +-----------+           +-------------+
                          | Peminjaman|           | SMTP Server |
                          | Database  |           | (Email)     |
                          +-----------+           +-------------+
```

## 🔧 Instalasi dan Pengaturan

### Prasyarat
- PHP 8.1 atau lebih tinggi
- Composer
- MySQL/MariaDB
- Node.js dan NPM
- Server web (Apache/Nginx)

### Langkah Instalasi
1. Clone repositori ini
   ```bash
   git clone https://github.com/username/perpustakaan-mtsn6.git
   cd perpustakaan-mtsn6
   ```

2. Instal dependensi PHP
   ```bash
   composer install
   ```

3. Instal dependensi JavaScript
   ```bash
   npm install && npm run build
   ```

4. Atur file lingkungan
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Konfigurasi database dan SMTP di file `.env`

6. Jalankan migrasi database
   ```bash
   php artisan migrate --seed
   ```

7. Konfigurasikan layanan notifikasi 24 jam
   ```bash
   php artisan queue:table
   php artisan migrate
   ```

## 💡 Penggunaan

### Manajemen Buku
- Tambahkan buku baru melalui menu "Tambah Buku"
- Atur kategori, jumlah stok, dan detail lainnya
- Unggah foto sampul buku (opsional)
- Generate QR code untuk buku secara otomatis dengan logo sekolah terintegrasi
- Download dan cetak QR code untuk ditempel pada buku fisik

### <a name="qr-code"></a>Teknologi QR Code
- QR Code dihasilkan menggunakan library SimpleSoftwareIO/simple-qrcode
- Setiap QR code memiliki logo sekolah di tengahnya
- Level koreksi kesalahan tinggi (H) memastikan QR code dapat dipindai meski ada kerusakan sebagian
- QR code langsung mengarahkan ke halaman peminjaman buku yang sesuai
- Dapat dipindai dengan aplikasi kamera standar atau aplikasi QR scanner
- Format PNG dengan resolusi tinggi untuk pencetakan berkualitas

### Peminjaman Buku
- Pindai QR code atau pilih buku dari daftar katalog
- Isi formulir peminjaman dengan batas waktu pengembalian (maksimal 3 hari)
- Sistem secara otomatis menurunkan stok buku dan memperbarui status ketersediaan
- Pantau status peminjaman melalui dashboard interaktif
- Batasan satu buku per pengguna untuk memastikan pemerataan akses

### Notifikasi & Pengingat
- Pengingat email dikirim otomatis sebelum batas waktu pengembalian
- Notifikasi terlambat dikirim untuk buku yang belum dikembalikan
- Status buku berubah otomatis menjadi "Terlambat" saat melewati batas waktu
- Antrian email menggunakan Laravel Queue untuk memastikan pengiriman yang andal
- Service Windows berjalan 24/7 untuk memproses antrian notifikasi

### Pemeliharaan Sistem
- Pantau log aplikasi di direktori `storage/logs/`
- Cek status layanan notifikasi Windows Service melalui panel NSSM
- Ikuti panduan troubleshooting untuk mengatasi masalah umum
- Dokumentasi lengkap untuk pemeliharaan jangka panjang sistem

## 📝 Lisensi

Sistem Informasi Perpustakaan MTSN 6 Garut dilisensikan di bawah [Lisensi MIT](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan kode dengan atribusi yang sesuai.
