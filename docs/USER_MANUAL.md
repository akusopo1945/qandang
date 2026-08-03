# Qandang User Manual (Panduan Pengguna)

## 1. Pendahuluan
Qandang adalah sistem monitoring ternak kambing berbasis digital. Manual ini membantu Anda memahami cara mengelola data ternak menggunakan Web Dashboard dan Mobile App.

## 2. Pendaftaran & Manajemen Ternak
### Melalui Web
- Masuk ke menu **Data Kambing**.
- Klik **Tambah**. Isi data lengkap termasuk foto profil utama.
- **Tujuan Pemeliharaan**: Pilih antara **Penggemukan (Fattening)** atau **Pembibitan (Breeding)**.
    - **Penggemukan**: Anda dapat mengisi **Target Berat**. Dashboard akan memantau ADG (Average Daily Gain).
    - **Pembibitan**: Khusus betina, Anda dapat memantau **Status Reproduksi** (Kosong, Birahi, Bunting, Menyusui, Kering).
- **Estimasi Kelahiran (HPL)**: Jika status diubah menjadi **Bunting**, sistem otomatis menghitung HPL (150 hari ke depan).
- **Silsilah**: Anda bisa memilih Induk dan Bapak dari daftar kambing yang sudah ada.
- **Marketplace**: Aktifkan status `for_sale` atau `auction` agar kambing muncul di katalog publik.
- Kode QR akan dihasilkan otomatis dengan format kompleks (QDG-YYG-BRE-HASH) dan bisa didownload untuk dicetak.

### Melalui Mobile
- Buka menu **Ternak**. Klik tombol **Tambah Ternak** di pojok kiri bawah.
- Gunakan fitur **Search & Filter** di daftar ternak untuk mencari kambing berdasarkan nama, ID, atau ras.

## 3. Identifikasi & Scanning
- Klik tombol **Scan QR** di pojok kanan bawah aplikasi mobile.
- Arahkan ke tag telinga kambing.
- **Quick Action**: Masukkan berat badan terbaru atau catat tindakan kesehatan langsung dari layar scan tanpa harus membuka profil lengkap.

## 4. Analisis Prediksi AI (Phase 2)
Sistem menggunakan kecerdasan buatan untuk memprediksi pertumbuhan.

### Cara Menjalankan
- **Web**: Klik tombol **AI Prediksi** pada tabel kambing. Tunggu progres bar hingga 100%.
- **Mobile**: Buka detail kambing, klik **JALANKAN ANALISIS & FORECAST**.

### Fitur Analisis
- **Forecast Grafik**: Garis biru menunjukkan estimasi berat badan bulan depan.
- **Health Score**: Skor (0-100) kondisi kesehatan berdasarkan analisis pola pertumbuhan.
- **Rekomendasi AI**: Saran otomatis mengenai pakan atau tindakan medis.

## 5. Galeri Foto & Zoom
- Setiap foto yang diambil saat mencatat kesehatan (vaksin/obat) akan otomatis tersimpan di **Galeri Foto** pada detail kambing.
- Klik foto apapun untuk memperbesar (**Full Zoom**) dan gunakan dua jari untuk memperbesar detail fisik ternak.

## 6. Silsilah Visual (Pedigree)
- Pada halaman detail kambing, terdapat bagian **Visual Silsilah**.
- Anda bisa melihat garis keturunan (Bapak & Induk) secara grafis untuk menghindari *inbreeding* (perkawinan sedarah).

## 7. Notifikasi Pengingat
- Aplikasi akan memberikan pengingat otomatis setiap **jam 08:00 pagi** jika ada jadwal kesehatan (vaksinasi/obat cacing) hari ini.
- Pastikan HP tidak dalam mode "Jangan Ganggu" agar notifikasi terdengar.

## 8. Monitoring Kandang (Phase 3 Placeholder)
- Klik kartu **Monitoring Kandang** di dashboard mobile.
- Pantau suhu, kelembaban, dan kadar amonia kandang secara real-time (saat ini masih mode simulasi untuk persiapan integrasi IoT).

## 9. Sinkronisasi & Ekspor
- **Sync**: Klik ikon dua panah di pojok kanan atas dashboard mobile untuk mengirim data yang tersimpan saat offline.
- **Ekspor CSV**: Masuk ke menu **Profil** > **Ekspor Data Ternak (CSV)** untuk mendapatkan laporan dalam format Excel.
