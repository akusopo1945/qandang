# Qandang
Smart Livestock Monitoring System for Goat Farming

---

# Project Overview
Platform smart farming berbasis mobile dan web untuk monitoring ternak kambing digital menggunakan QR Code.

# Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend Web:** Laravel Filament (Admin & Analytics)
- **Mobile:** Flutter (Field Operations, Offline-first)
- **AI Service:** Python FastAPI (Growth Prediction)
- **Database:** PostgreSQL
- **IoT:** MQTT (ESP32 Sensors)

# Directory Structure
- `/backend`: Laravel API & Filament Dashboard
- `/frontend-mobile`: Flutter App
- `/ai-service`: Python FastAPI Prediction Engine
- `/iot`: Firmware & MQTT Config
- `/docs`: Technical Documentation

# Core Features
1. **QR Identification:** Unique digital identity per goat.
2. **Monitoring:** Weight tracking & growth charts.
3. **Health Records:** Vaccination & medical history.
4. **AI Prediction:** Growth & health scoring (Phase 2).
5. **IoT Integration:** Real-time barn environment monitoring (Phase 3).

# Deployment Status
- **URL:** https://qandang.duckdns.org/
- **Architecture:** 
    - **Nginx:** Reverse proxy to Gateway (4500)
    - **Gateway (Node.js):** Proxy to Backend (8000) and AI Service (8500)
    - **Backend (PHP):** Running in "Lite Mode" due to missing framework files. Entry: `backend/public/index.php`.
    - **AI Service (FastAPI):** Operational on port 8500.
- **PM2 Processes:** `qandang-gateway`, `qandang-backend`, `qandang-ai`.

# Development Mandates
- **Fast in the Field:** Minimum clicks for outdoor use.
- **Offline First:** Local data sync for mobile.
- **Security:** Strict data privacy for farm records.
