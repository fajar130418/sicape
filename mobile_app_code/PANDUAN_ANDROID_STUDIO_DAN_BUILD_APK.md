# Panduan Menjalankan & Membangun APK dengan Android Studio

Aplikasi mobile Sicape ini dibangun menggunakan framework Flutter. Berikut adalah panduan langkah demi langkah untuk membuka proyek ini di Android Studio, menjalankannya di emulator/perangkat asli, dan mem-build file APK agar bisa diinstal di HP Android.

## Prasyarat
1. Pastikan **Android Studio** sudah terinstal.
2. Pastikan plugin **Flutter** dan **Dart** sudah terinstal di dalam Android Studio (bisa dicek di menu `File > Settings > Plugins`).
3. Pastikan **XAMPP (Apache & MySQL)** sedang berjalan di komputer Anda karena aplikasi membutuhkan API dari backend.

---

## 1. Membuka Proyek di Android Studio
1. Buka aplikasi **Android Studio**.
2. Jika berada di layar awal (Welcome Screen), klik **Open**. Jika project lain sedang terbuka, klik menu **File > Open**.
3. Arahkan direktori (path) ke folder proyek mobile app Sicape. Biasanya lokasinya ada di: `E:\Server3\www\sicape\mobile_app_code`
4. Pilih folder `mobile_app_code` tersebut, lalu klik **OK**.
5. Tunggu proses *indexing* dan sinkronisasi awal selesai di Android Studio (perhatikan loading bar di pojok kanan bawah).

## 2. Inisiasi Dependensi (Pub Get)
1. Setelah proyek terbuka, cari dan buka file `pubspec.yaml` (ada di struktur sebelah kiri "Project").
2. Di bagian atas teks editor file `pubspec.yaml`, biasanya muncul banner "Flutter commands". Klik tombol **Pub get**.
3. Atau, buka tab **Terminal** di bagian bawah Android Studio dan ketikkan command:
   ```bash
   flutter pub get
   ```
   Tunggu hingga proses unduh plugin (library) selesai tanpa pesan error (exit code 0).

## 3. Menyesuaikan Konfigurasi Server (Penting!)
Agar aplikasi dapat terhubung dengan backend / API secara benar, Anda perlu menyesuaikan IP address di file koneksi.

1. Buka file `lib/services/api_service.dart`.
2. Cari baris untuk `baseUrl` (biasanya `static const String baseUrl = ...`).
3. Sesuaikan URL dengan IP yang sesuai:
    * **Bila menggunakan Emulator bawaan Android Studio**: Ganti menjadi `http://10.0.2.2/sicape/public/api`. (IP `10.0.2.2` adalah alias khusus emulator Android ke localhost PC Anda).
    * **Bila menggunakan HP Fisik (Kabel USB) atau Emulator Genymotion/Nox dll**: Ganti menjadi alamat IPv4 jaringan Wi-Fi/LAN PC Anda. (Gunakan command prompt ketik `ipconfig` untuk melihat IP, contohnya `http://192.168.1.5/sicape/public/api`).

## 4. Menjalankan Aplikasi (Debug/Run)
1. Pergi ke bagian **Toolbar atas** Android Studio (sebelah tombol run hijau).
2. Dari menu drop-down *Device*, pilih **Device target** Anda. Anda bisa memilih:
   - Emulator yang sudah dibuat (misal: Pixel 6 API 34).
   - Menjalankan "Device Manager" jika emulator belum ada dan ingin membuat Emulator (Virtual Device) baru.
   - HP fisik Anda (jika developer USB Debugging sudah aktif di HP & dicolok).
3. Setelah device terpilih, klik icon **Run** (segitiga warna hijau) atau tekan **Shift + F10**.
4. Tunggu proses build Gradle dan instalasi ke device (proses pertama kali run biasanya sedikit memakan waktu ±2-5 menit tergantung spesifikasi PC).

---

## 5. Merilis dan Membangun File APK (.apk)
Apabila aplikasi sudah berjalan dengan baik dan siap didistribusikan ke rekan/karyawan untuk diinstal:

1. Pergi ke tab **Terminal** (di bagian paling bawah layar Android Studio).
2. Pastikan posisi terminal berada di folder proyek (contoh: `E:\Server3\www\sicape\mobile_app_code>`).
3. Ketikkan perintah berikut untuk membangun APK:
   ```bash
   flutter build apk --release
   ```
4. Tekan **Enter**. Tunggu proses *building* selesai. Gradle akan melakukan _compile_, _shrink_, dan optimasi aplikasi.
5. Jika telah selesai dan sukses, terminal akan memunculkan tulisan berwarna terang yang menunjukkan rute output file APK, biasanya seperti:
   **`✓ Built build\app\outputs\flutter-apk\app-release.apk`**
6. Untuk mengambil file APK-nya, buka **File Explorer** Windows Anda.
7. Arahkan ke rute direktori ini:
   `E:\Server3\www\sicape\mobile_app_code\build\app\outputs\flutter-apk\`
8. File bernama `app-release.apk` siap untuk dikirim / disebarkan (via WhatsApp, Google Drive, dsb.) dan dapat diinstal langsung ke perangkat Android pengguna.

---
### Solusi Masalah Umum (Troubleshooting)
- Jika proses build Apk mengalami error Gradle, pastikan **koneksi internet stabil**, atau coba lakukan _clean build_ melalui terminal Android Studio: `flutter clean` lalu ulangi `flutter pub get`.
- Jika sesudah diinstal di device asli API/Login gagal/Loading terus, perhatikan kembali **Langkah No 3**. Pastikan perangkat mobile dan PC penyedia Server API (XAMPP) berada dalam **Satu Jaringan Wi-Fi/LAN yang sama** jika Anda memakai IP Local (192.168.x.x).
