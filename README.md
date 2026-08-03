# 🐐 Qandang - Smart Livestock Monitoring System

[![Build Qandang APK](https://github.com/akusopo1945/qandang/actions/workflows/build_apk.yml/badge.svg)](https://github.com/akusopo1945/qandang/actions/workflows/build_apk.yml)

![Qandang Banner](backend/public/images/og-image.jpg)

**Qandang** is a modern Smart Farming platform designed for digital goat livestock monitoring using QR Codes and IoT integration. It helps farmers track growth, health records, and barn environments in real-time.

---

## 📱 Mobile App (APK)

Aplikasi mobile dibangun secara otomatis menggunakan GitHub Actions. 

**Cara Download APK:**
1. Pergi ke tab **[Actions](https://github.com/akusopo1945/qandang/actions)** di repository ini.
2. Klik pada workflow run terbaru yang berwarna hijau (sukses).
3. Scroll ke bawah ke bagian **Artifacts**.
4. Download file **qandang-release-apk**.

---

## 🚀 Key Features

1.  **QR Identification & Marketplace**: Unique digital identity per goat with a public catalog for selling and auctions.
2.  **Marketplace Integration**: Built-in storefront with Hero Banner, Catalog, Wishlist, and Checkout system.
3.  **Health Records**: Track vaccinations, medical history, and weight logs.
3.  **Fattening & Breeding Tracking**: Dedicated workflows for weight gain targets (Fattening) and reproductive status monitoring with automated HPL (Estimated Delivery Date) for Breeding.
4.  **AI Growth Prediction (Phase 2)**: Dynamic charts and AI-powered growth forecasts with health scoring and recommendations.
4.  **Image Gallery & Zoom**: Unified gallery for profile and health documentation with pinch-to-zoom viewer.
5.  **Visual Pedigree**: Graphical family tree (Sire/Dam) tracking.
6.  **Local Notifications**: Automatic morning reminders for scheduled health actions.
7.  **IoT Integration (Phase 3 Readiness)**: Real-time barn environment monitoring dashboard (placeholder).
8.  **Offline-First Mobile**: Designed for field operations with local SQLite sync.

---

## 📖 Documentation

- **[User Manual (Peternak)](docs/USER_MANUAL.md)**: Panduan lengkap manajemen ternak.
- **[Marketplace & QR Manual](docs/MARKETPLACE_MANUAL.md)**: Detail fitur katalog, checkout, dan skema kode QR.
- **AI Prediksi**: Fitur unggulan untuk memprediksi berat badan ternak di bulan mendatang.

## 🛠 Tech Stack

-   **Backend**: Laravel 12 (PHP 8.2+) & Filament Dashboard
-   **Frontend Web**: Tailwind CSS & Alpine.js
-   **Mobile**: Flutter (Dart)
-   **AI Service**: Python FastAPI (Growth Prediction)
-   **Database**: PostgreSQL
-   **IoT**: MQTT (ESP32 Sensors)
-   **Gateway**: Node.js Express Proxy

---

## 📂 Directory Structure

-   `/backend`: Laravel API, Filament Admin & Analytics.
-   `/frontend_mobile`: Flutter application for field use.
-   `/ai-service`: Python FastAPI prediction engine.
-   `/iot`: Firmware and MQTT configurations.
-   `/docs`: Technical documentation.

---

## ⚙️ Installation & Setup

### 1. Gateway (Root)
```bash
npm install
npm start # Runs on port 4501
```

### 2. Backend (Laravel)
```bash
cd backend
composer install
npm install
npm run build
php artisan migrate --seed
php artisan serve --port=8001
```

### 3. AI Service (FastAPI)
```bash
cd ai-service
python -m venv venv
source venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --port 8501
```

---

## 🌐 Deployment

Current production setup:
-   **URL**: [https://qandang.duckdns.org/](https://qandang.duckdns.org/)
-   **Process Manager**: PM2 handles `qandang-gateway`, `qandang-backend`, and `qandang-ai`.

---

## 👥 Authors
Developed by **CakDoel & theGong** © 2026 Qandang. All rights reserved.
