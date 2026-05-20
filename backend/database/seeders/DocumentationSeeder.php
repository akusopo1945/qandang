<?php

namespace Database\Seeders;

use App\Models\Documentation;
use Illuminate\Database\Seeder;

class DocumentationSeeder extends Seeder
{
    public function run(): void
    {
        Documentation::create([
            'title' => 'Panduan Memulai Qandang',
            'category' => 'Panduan Pengguna',
            'content' => "# Selamat Datang di Qandang\n\nQandang adalah sistem monitoring ternak kambing pintar. Berikut langkah awal penggunaan:\n\n1. **Daftarkan Kambing**: Masuk ke menu 'Data Kambing' dan tambahkan data baru (termasuk Bapak/Induk).\n2. **Identifikasi QR**: Cetak kode QR yang tersedia untuk ditempel pada telinga atau kandang ternak.\n3. **Monitoring Rutin**: Gunakan Scan QR di Mobile untuk input berat badan secara cepat.\n4. **Notifikasi**: Aktifkan izin notifikasi di HP untuk menerima pengingat jadwal kesehatan setiap pagi jam 08:00.",
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
            'title' => 'Arsitektur Teknis',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Detail Teknis Qandang\n\n- **Backend**: Laravel 12 (PHP 8.2+)\n- **Frontend**: Filament 3 & Livewire (Dashboard Admin)\n- **Mobile**: Flutter 3.27 (Offline-First)\n- **AI Engine**: Mimo-v2.5-Pro (LLM with RAG-like prompting)\n- **Database**: PostgreSQL\n- **Proxy**: Nginx + PM2 Process Manager",
            'order' => 3,
        ]);
    }
}
