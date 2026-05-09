# Panduan Build Aplikasi Mobile Qandang 🐐

Aplikasi mobile Qandang menggunakan Flutter. Karena build membutuhkan Android SDK, Anda punya dua pilihan cara mendapatkan file APK.

---

## Opsi 1: Otomatis via GitHub Actions (Sangat Disarankan)
Saya sudah membuatkan skrip otomatis. Anda tidak perlu instal apapun di laptop.
1. **Push** kode ini ke repository GitHub Anda.
2. Buka tab **Actions** di halaman GitHub Anda.
3. Anda akan melihat proses "Build Qandang APK" berjalan.
4. Setelah selesai (sekitar 5-7 menit), klik pada hasil build tersebut.
5. Di bagian **Artifacts**, download file `qandang-release-apk`.
6. Ekstrak dan instal di HP Android Anda.

---

## Opsi 2: Build Manual di Laptop Lokal
Gunakan cara ini jika Anda ingin melakukan kustomisasi kode secara cepat.

### 1. Persiapan
- Instal **Flutter SDK**: [https://docs.flutter.dev/get-started/install](https://docs.flutter.dev/get-started/install)
- Instal **Android Studio** (untuk mendapatkan Android SDK).
- Pastikan HP Android sudah dalam mode *Developer Options*.

### 2. Langkah Build
Buka terminal/CMD, masuk ke folder project:
```bash
cd frontend-mobile
```

Jalankan perintah ambil library:
```bash
flutter pub get
```

Jalankan build APK:
```bash
flutter build apk --release
```

### 3. Lokasi File
File APK hasil build berada di:
`build/app/outputs/flutter-apk/app-release.apk`

---

## Catatan Penting API
Aplikasi ini sudah dikonfigurasi untuk terhubung ke:
`https://qandang.duckdns.org/api`

Pastikan server backend Anda dalam keadaan menyala agar aplikasi mobile bisa login.

**Email Login Default:** `admin@qandang.org`
**Password Login Default:** `password`
