<?php

namespace Database\Seeders;

use App\Models\Documentation;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing documents for clean state
        \DB::statement('TRUNCATE TABLE documentations RESTART IDENTITY CASCADE');

        Documentation::create([
            'title' => 'Panduan Memulai Qandang',
            'category' => 'Panduan Pengguna',
            'content' => "# Selamat Datang di Qandang\n\nQandang adalah platform monitoring ternak kambing digital berbasis smart farming. Langkah awal penggunaan:\n\n1. **Daftarkan Kambing**: Masuk ke menu 'Data Kambing' dan tambahkan data baru (termasuk Bapak/Induk).\n2. **Identifikasi QR**: Cetak kode QR yang tersedia untuk ditempel pada telinga atau kandang ternak.\n3. **Monitoring Rutin**: Gunakan Scan QR di Mobile untuk input berat badan secara cepat.\n4. **Notifikasi**: Aktifkan izin notifikasi di HP untuk menerima pengingat jadwal kesehatan setiap pagi jam 08:00.",
            'order' => 1,
        ]);

        Documentation::create([
            'title' => 'Fitur AI & Silsilah',
            'category' => 'Panduan Pengguna',
            'content' => "# Fitur Unggulan Qandang\n\n### 1. Prediksi Pertumbuhan AI\nKlik tombol **AI Prediksi** untuk melihat estimasi berat bulan depan. AI menganalisis histori pertumbuhan untuk memberikan rekomendasi pakan.\n\n### 2. Silsilah Visual (Pedigree)\nDi halaman detail kambing, Anda bisa melihat diagram keturunan. Fitur ini sangat berguna untuk perencanaan pembiakan agar tidak terjadi perkawinan sedarah.\n\n### 3. Galeri Foto Detail\nSemua foto dokumentasi kesehatan akan terkumpul otomatis di galeri kambing tersebut. Klik foto untuk memperbesar dengan fitur zoom.",
            'order' => 2,
        ]);

        Documentation::create([
            'title' => 'Monitoring IoT (Persiapan)',
            'category' => 'Panduan Pengguna',
            'content' => "# Monitoring Kandang Real-time\n\nSaat ini Qandang sedang mempersiapkan integrasi sensor fisik (Phase 3). Anda sudah bisa melihat dashboard **Monitoring Kandang** di menu mobile yang nantinya akan menampilkan data asli dari sensor:\n- Suhu & Kelembaban\n- Kadar Gas Amonia\n- Kontrol Kipas Otomatis",
            'order' => 3,
        ]);

        Documentation::create([
            'title' => 'Marketplace & Identifikasi QR',
            'category' => 'Panduan Pengguna',
            'content' => "# Fitur Jual-Beli & Kode QR\n\n### 1. Skema Kode QR Baru\nSetiap kambing memiliki identitas unik dengan format: **QDG-[TAHUN][GEL] - [RAS] - [HASH]**.\nContoh: `QDG-26M-ETW-A8B2` (2026, Jantan, Etawa, Kode Keamanan A8B2).\n\n### 2. Cara Menjual Kambing\n- Klik **Edit** pada Data Kambing.\n- Buka bagian **Marketplace**.\n- Atur status ke `Dijual` atau `Dilelang` dan isi harganya.\n- Aktifkan `Featured` jika ingin kambing muncul di halaman depan web utama.\n\n### 3. Alur Checkout\nPembeli bisa memasukkan kambing ke keranjang dan melakukan checkout. Setelah checkout, sistem akan membuat pesanan (Order) dan Admin akan menghubungi pembeli untuk proses pembayaran manual.",
            'order' => 4,
        ]);

        Documentation::create([
            'title' => 'Panduan Cetak & Tempel Tag QR',
            'category' => 'Panduan Pengguna',
            'content' => "# Panduan Cetak & Tempel Tag QR\n\n### 1. Spesifikasi Cetak\n- **Bahan**: PVC ear-tag tahan air & UV atau stiker laminasi outdoor.\n- **Ukuran**: Minimal 3cm x 3cm agar kamera HP mudah fokus.\n- **Kontras**: Gunakan latar putih bersih dengan pola hitam solid.\n\n### 2. Pemasangan Fisik\n- **Ear-Tag**: Pasang menggunakan applicator ear-tag di bagian tengah telinga (hindari pembuluh darah besar).\n- **Kandang**: Cetak versi besar (10cm x 10cm) dan tempel di tiang depan masing-masing sekat kandang.",
            'order' => 5,
        ]);

        Documentation::create([
            'title' => 'Arsitektur Teknis',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Detail Teknis Qandang\n\n- **Backend**: Laravel 12 (PHP 8.2+)\n- **Frontend**: Filament 3 & Livewire (Dashboard Admin)\n- **Mobile**: Flutter 3.27 (Offline-First)\n- **AI Engine**: Python FastAPI (Growth Prediction)\n- **Database**: PostgreSQL\n- **Proxy**: Nginx + PM2 Process Manager",
            'order' => 6,
        ]);

        Documentation::create([
            'title' => 'Integrasi AI Growth Prediction',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Integrasi Layanan Prediksi AI\n\nLayanan AI berjalan menggunakan Python FastAPI di port `8500` (atau subpath `/ai` via Gateway).\n\n### 1. Endpoint Prediksi Berat\n- **URL**: `POST /ai/predict-weight`\n- **Payload JSON**:\n  ```json\n  {\n    \"initial_weight\": 15.0,\n    \"current_weight\": 52.5,\n    \"age_months\": 12,\n    \"breed\": \"Boer\"\n  }\n  ```\n- **Response JSON**:\n  ```json\n  {\n    \"predicted_weight_next_month\": 57.7,\n    \"growth_rate\": 0.98\n  }\n  ```",
            'order' => 7,
        ]);

        Documentation::create([
            'title' => 'Protokol Sensor IoT (MQTT)',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Protokol IoT Kandang Kambing\n\nSensor lingkungan kandang mengirimkan data secara berkala menggunakan protokol MQTT.\n\n### 1. Konfigurasi Broker\n- **Host**: `qandang.duckdns.org`\n- **Port**: `1883` (Non-SSL) / `8883` (SSL)\n- **Client ID**: `esp32-barn-[MAC_ADDRESS]`\n\n### 2. Topik & Payload\n- **Topik**: `qandang/barn/sensors`\n- **Payload JSON**:\n  ```json\n  {\n    \"barn_id\": 1,\n    \"temperature\": 28.4,\n    \"humidity\": 65.2,\n    \"ammonia_level\": 12.5\n  }\n  ```",
            'order' => 8,
        ]);

        Documentation::create([
            'title' => 'Troubleshooting & Pemeliharaan',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Troubleshooting & Pemeliharaan\n\n### 1. Database Backup\nUntuk melakukan backup PostgreSQL manual:\n```bash\npg_dump -U postgres qandang > backup.sql\n```\n\n### 2. Reset / Restart Layanan\nJika dashboard melambat atau antrean data macet, jalankan perintah ini:\n```bash\n# Restart Laravel Queue & Cache\nphp artisan queue:restart\nphp artisan cache:clear\n\n# Restart Node Gateway & Python AI di PM2\npm2 restart qandang-gateway qandang-ai\n```",
            'order' => 9,
        ]);
    }
}
