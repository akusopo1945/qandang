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
            'content' => "# Selamat Datang di Qandang\n\nQandang adalah sistem monitoring ternak kambing pintar. Berikut langkah awal penggunaan:\n\n1. **Daftarkan Kambing**: Masuk ke menu 'Data Kambing' dan tambahkan data baru.\n2. **Identifikasi QR**: Setelah mendaftar, cetak kode QR yang tersedia untuk ditempel pada ternak.\n3. **Monitoring Berat**: Lakukan penimbangan rutin dan catat di menu 'Monitoring Berat'.\n4. **Analisis AI**: Gunakan tombol 'AI Prediksi' (Web) atau 'JALANKAN ANALISIS' (Mobile) untuk mendapatkan prediksi pertumbuhan dan skor kesehatan.\n\n### Fitur Mobile Baru\n- **Scan QR Cepat**: Arahkan kamera ke tag telinga kambing untuk melihat ringkasan info.\n- **Tambah Ternak**: Tombol pendaftaran kini berada di pojok kiri bawah layar Daftar Ternak.\n- **Profil & Bantuan**: Akses menu profil untuk ekspor data CSV dan panduan lengkap.",
            'order' => 1,
        ]);

        Documentation::create([
            'title' => 'Fitur Prediksi AI (Phase 2)',
            'category' => 'Panduan Pengguna',
            'content' => "# Analisis Kecerdasan Buatan\n\nQandang menggunakan AI untuk membantu peternak mengambil keputusan:\n\n### Cara Menggunakan\n1. **Pilih Kambing**: Buka detail kambing di Web Dashboard atau Mobile App.\n2. **Trigger AI**: Klik tombol **AI Prediksi**. Sistem akan menampilkan animasi loading saat AI menganalisis data.\n3. **Hasil Analisis**:\n   - **Forecast Berat**: Prediksi berat badan untuk 1 bulan ke depan.\n   - **Health Score**: Skor keyakinan AI terhadap kondisi kesehatan ternak.\n   - **Rekomendasi**: Saran praktis terkait pakan, vaksin, atau perawatan medis.\n\n*Catatan: Akurasi AI meningkat seiring dengan bertambahnya data historis penimbangan (minimal 3-5 catatan).* ",
            'order' => 2,
        ]);

        Documentation::create([
            'title' => 'Arsitektur Teknis',
            'category' => 'Dokumentasi Teknis',
            'content' => "# Detail Teknis Qandang\n\n- **Backend**: Laravel 12 (PHP 8.2+)\n- **Frontend**: Filament 3 & Livewire (Dashboard Admin)\n- **Mobile**: Flutter 3.27 (Offline-First)\n- **AI Engine**: Mimo-v2.5-Pro (LLM with RAG-like prompting)\n- **Database**: PostgreSQL\n- **Proxy**: Nginx + PM2 Process Manager",
            'order' => 3,
        ]);
    }
}
