# Qandang Identifikasi & Marketplace Manual

## 1. Skema Identifikasi QR Code
Qandang menggunakan format kode unik yang kompleks untuk memastikan keamanan dan kemudahan identifikasi di lapangan.

**Format Kode:** `QDG-[YY][G]-[BRE]-[HASH]`

| Komponen | Arti | Contoh |
| :--- | :--- | :--- |
| **QDG** | Prefix Brand (Qandang) | `QDG` |
| **YY** | Dua digit tahun kelahiran/pendaftaran | `26` (2026) |
| **G** | Inisial Jenis Kelamin | `M` (Male/Jantan), `F` (Female/Betina) |
| **BRE** | Tiga huruf pertama Ras/Jenis | `ETW` (Etawa), `BOE` (Boer) |
| **HASH** | 4 digit kode acak unik (keamanan) | `A8B2` |

**Contoh Lengkap:** `QDG-26M-ETW-A8B2`
*Artinya: Kambing Qandang, kelahiran 2026, Jantan, jenis Etawa, dengan pengenal unik A8B2.*

---

## 2. Fitur Marketplace (Katalog Publik)
Peternak dapat memasarkan ternaknya langsung melalui landing page Qandang.

### Cara Menampilkan Kambing di Katalog:
1. Masuk ke **Admin Dashboard** > **Data Kambing**.
2. Klik **Edit** pada kambing yang ingin dijual.
3. Buka bagian **Marketplace (Katalog & Lelang)**.
4. **Status Jual**: Ubah ke `Dijual (Katalog)` atau `Dilelang`.
5. **Harga**: Isi harga jual dalam Rupiah.
6. **Featured**: Aktifkan toggle `Featured` jika ingin kambing muncul di Banner Utama (Hero Section) halaman depan.

### Alur Pembelian bagi Pengunjung:
1. **Pencarian**: Pengunjung melihat katalog di halaman utama.
2. **Detail**: Klik tombol "Lihat Detil" untuk melihat spek lengkap (Bobot, Tinggi, Umur, Jenis).
3. **Wishlist**: Pengunjung dapat memasukkan kambing ke wishlist (tanpa login, data tersimpan di browser).
4. **Keranjang & Checkout**:
    - Pengunjung wajib **Login/Daftar**.
    - Masukkan ke keranjang dan klik **Checkout**.
    - Sistem akan mencatat pesanan (`Order`) dan memberitahu Admin.
    - Admin akan menghubungi pembeli untuk proses pembayaran dan pengiriman manual.

---

## 3. Integrasi Scanning (Universal QR)
QR Code Qandang didesain agar fleksibel:

- **Scan via Kamera HP (Umum):** Akan langsung membuka halaman detail publik di web browser. Cocok untuk calon pembeli yang ingin melihat data cepat tanpa instal aplikasi.
- **Scan via Aplikasi Qandang (Peternak):** Akan membuka profil lengkap internal, termasuk riwayat medis dan input berat badan. Jika aplikasi terinstal, sistem web akan mencoba melakukan *Deep Linking* (membuka aplikasi secara otomatis).

---

## 4. Keamanan Data
- **Publik**: Hanya melihat data fisik dan harga.
- **Internal (Admin)**: Melihat data sensitif seperti riwayat penyakit, catatan vaksin detail, dan biaya perawatan.
