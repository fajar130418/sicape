# SICAPE Mobile App

Aplikasi Mobile untuk Sistem Informasi Cuti Pegawai (SICAPE) menggunakan Flutter.

## Persyaratan
1.  Install **Flutter SDK**: [https://flutter.dev/docs/get-started/install](https://flutter.dev/docs/get-started/install)
2.  Install **Android Studio** atau **VS Code** dengan ekstensi Flutter.
3.  Pastikan Server Backend (XAMPP) berjalan.

## Cara Menjalankan

1.  Buka terminal/cmd dan masuk ke folder ini:
    ```bash
    cd mobile_app_code
    ```

2.  Download dependencies:
    ```bash
    flutter pub get
    ```

3.  **PENTING**: Konfigurasi IP Address
    - Buka file `lib/services/api_service.dart`.
    - Ubah `baseUrl` sesuai dengan IP komputer Anda.
    - Jika menggunakan **Android Emulator**, gunakan `http://10.0.2.2/sicape/public/api`.
    - Jika menggunakan **HP Fisik** (via USB/Wifi), gunakan IP LAN komputer Anda (contoh: `http://192.168.1.10/sicape/public/api`).
    - Pastikan HP dan Laptop terhubung di jaringan (Wifi) yang sama.

4.  Jalankan aplikasi:
    ```bash
    flutter run
    ```

## Fitur
- **Login**: Menggunakan NIP dan Password pegawai.
- **Dashboard**: Melihat sisa cuti dan riwayat terakhir.
- **Ajukan Cuti**: Form pengajuan cuti baru.
- **Persetujuan**: (Khusus Supervisor/Kepala Dinas) Menyetujui atau menolak permohonan.
