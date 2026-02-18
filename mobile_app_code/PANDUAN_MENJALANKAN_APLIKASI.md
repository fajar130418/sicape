# Panduan Menjalankan Aplikasi dengan Extension Flutter di VS Code

Karena perintah `flutter` tidak terdeteksi secara otomatis di terminal saya, Anda dapat menjalankannya dengan mudah menggunakan **Extension Flutter** yang sudah terinstall di VS Code Anda. Berikut adalah langkah-langkah detailnya:

## 1. Buka Proyek di VS Code
Karena folder proyek saat ini berada di dalam `sicape/mobile_app_code`, Anda perlu membukanya sebagai **root folder** agar extension Flutter mendeteksinya dengan benar.

1.  Di VS Code, pilih menu **File** > **Open Folder...**
2.  Arahkan ke: `c:\xampp\htdocs\sicape\mobile_app_code`
3.  Klik **Select Folder**.

> **Penting**: Jika Anda membuka folder `sicape` (induk), terkadang extension Flutter bingung mana file `pubspec.yaml` utamanya. Lebih aman membuka langsung folder `mobile_app_code`.

## 2. Inisialisasi Dependensi (Library)
Saat folder terbuka, VS Code biasanya akan mendeteksi file `pubspec.yaml` dan mungkin menampilkan notifikasi di pojok kanan bawah: *"Some packages are missing or out of date"*.

1.  Klik tombol **Get Packages** pada notifikasi tersebut.
2.  Atau, buka Terminal di VS Code (`Ctrl + ` backtick) dan ketik manual:
    ```bash
    flutter pub get
    ```
    Tunggu hingga proses selesai (exit code 0).

## 3. Konfigurasi Koneksi API (Wajib)
Sebelum menjalankan, pastikan aplikasi bisa berkomunikasi dengan server XAMPP Anda.

1.  Buka file `lib/services/api_service.dart`.
2.  Cari baris `static const String baseUrl = ...`.
3.  Ganti `baseUrl` sesuai perangkat yang Anda gunakan:
    *   **Jika menggunakan Android Emulator**:
        Gunakan `http://10.0.2.2/sicape/public/api`
    *   **Jika menggunakan HP Fisik (USB Debugging)**:
        Gunakan IP Address komputer Anda (Cek dengan `ipconfig` di CMD), contoh:
        `http://192.168.1.10/sicape/public/api`
    *   **Jika Windows / Web**:
        Gunakan `http://localhost/sicape/public/api`

> **Tips**: Karena di screenshot VS Code Anda terlihat device `Windows (desktop)`, Anda bisa langsung menjalankannya di situ tanpa emulator Android. Cukup pastikan `baseUrl` menggunakan localhost.


> **Tips**: Pastikan server Apache & MySQL di XAMPP sudah berjalan (Start).

## 4. Pilih Perangkat (Device)
Di bagian bawah VS Code (Status Bar sebelah kanan), Anda akan melihat tulisan seperti `No Device` atau `Windows (desktop-x64)`.

1.  Klik tulisan tersebut.
2.  Pilih Emulator Android yang tersedia (misal: `Pixel_3a_API_34_extension_level_7_x86_64`).
3.  Tunggu hingga Emulator terbuka dan siap.

## 5. Jalankan Aplikasi (Run)
Ada dua cara untuk menjalankan:

*   **Cara 1 (Menu Run)**:
    Klik menu **Run** > **Start Debugging** (atau tekan tombol `F5`).

*   **Cara 2 (Tombol Play)**:
    Biasanya ada tombol "Play" kecil di pojok kanan atas editor saat membuka file `.dart`.

## Troubleshooting (Jika Gagal)
*   **Error: "CMD tidak dikenali"**: Pastikan path Flutter sudah masuk ke Environment Variables Windows Anda. Namun jika menggunakan extension, biasanya ia mencari sendiri lokasi SDK-nya.
*   **Error: "Connection Refused"**: Ini berarti aplikasi tidak bisa menghubungi server XAMPP. Cek kembali IP Address di langkah nomor 3. Matikan Firewall Windows sebentar jika perlu untuk tes.
*   **Error: "Gradle build failed"**: Pastikan koneksi internet stabil saat pertama kali build, karena Flutter perlu mendownload Gradle wrapper.

Selamat mencoba!
