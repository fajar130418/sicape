# E-Cuti ASN (Sistem Informasi Manajemen Cuti)

Aplikasi manajemen cuti ASN berbasis web yang mendukung akumulasi cuti 18-24 hari (FIFO) sesuai dengan regulasi terbaru.

## Fitur Utama
- **Manajemen Pegawai**: Input data pegawai beserta saldo cuti awal (migrasi data lama).
- **Pengajuan Cuti**: Mendukung berbagai tipe cuti (Tahunan, Besar, Sakit, Melahirkan, dll).
- **Logika ASN (FIFO)**: Pemotongan jatah cuti otomatis mulai dari tahun N-2, N-1, baru kemudian tahun berjalan (N).
- **Akumulasi Cuti**: Mendukung jatah cuti akumulasi hingga 24 hari kerja.
- **Cetak PDF**: Draft formulir cuti standar ASN otomatis dengan tanda tangan elektronik/manual.
- **Manajemen Hari Libur**: Membedakan Hari Libur Nasional dan Cuti Bersama (Cuti bersama tidak memotong jatah tahunan).

## Prasyarat
- PHP >= 8.1
- MySQL / MariaDB
- Composer
- XAMPP / Laragon (Disarankan)

## Panduan Instalasi (Perangkat Baru)

1. **Clone Repository**
   ```bash
   git clone https://github.com/fajar130418/e-cuti-asn.git
   cd cuti
   ```

2. **Instal Dependensi**
   Buka terminal di folder proyek dan jalankan:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   - Salin file `env` menjadi `.env`:
     ```bash
     cp env .env
     ```
   - Buka `.env` dan sesuaikan pengaturan database:
     ```env
     database.default.hostname = localhost
     database.default.database = cuti_db
     database.default.username = root
     database.default.password = 
     database.default.DBDriver = MySQLi
     ```

4. **Setup Database**
   Jalankan perintah berikut untuk membuat tabel dan mengisi data awal:
   ```bash
   php spark migrate
   php spark db:seed LeaveSeeder
   ```

5. **Jalankan Aplikasi**
   ```bash
   php spark serve
   ```
   Akses aplikasi di: `http://localhost:8080`

## Akun Login Default (Admin)
- **Username**: 199001012020011001
- **Password**: admin123

## Catatan Penting
- Folder `writable` harus memiliki izin akses tulis (write permission).
- Pastikan ekstensi PHP `intl` dan `mbstring` sudah aktif di `php.ini`.

---
Dikembangkan untuk: **Dispusip Seruyan**
