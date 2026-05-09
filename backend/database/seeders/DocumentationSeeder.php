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
            'content' => "# Selamat Datang di Qandang\n\nQandang adalah sistem monitoring ternak kambing pintar. Berikut langkah awal penggunaan:\n\n1. **Daftarkan Kambing**: Masuk ke menu 'Data Kambing' dan tambahkan data baru.\n2. **Identifikasi QR**: Setelah mendaftar, cetak kode QR yang tersedia untuk ditempel pada ternak.\n3. **Monitoring Berat**: Lakukan penimbangan rutin dan catat di menu 'Monitoring Berat'.\n4. **Analisis AI**: Gunakan tombol 'AI Prediksi' untuk mendapatkan saran perawatan.",
            'order' => 1,
        ]);

        Documentation::create([
            'title' => 'Arsitektur Teknis',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Detail Teknis Qandang\n\n- **Backend**: Laravel 12 (PHP 8.4)\n- **Frontend**: Filament 3 (TALL Stack)\n- **Database**: PostgreSQL\n- **Web Server**: Nginx + PHP-FPM\n- **Integrasi AI**: Xiaomi MiMo API (OpenAI Compatible)",
            'order' => 2,
        ]);
    }
}
