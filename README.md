# 🌱 SmartGarden

Sistem monitoring dan kontrol taman pintar berbasis IoT menggunakan ESP32 dan Laravel.

## Tech Stack

- **Backend** — Laravel 12 · PHP 8.2
- **Frontend** — Blade · Tailwind CSS v4 · Vite
- **Database** — MySQL / SQLite
- **Hardware** — ESP32 (sensor & pompa air)

## Fitur

- 📡 Monitoring sensor real-time (setiap 2 detik dari ESP32)
- 💧 Kontrol pompa air via dashboard web
- 📊 Riwayat data sensor (50 data terakhir)
- 🔌 Status koneksi ESP32

## Instalasi

```bash
# Clone & setup
git clone <repo-url>
cd smartgarden
composer run setup
```

Script `setup` otomatis menjalankan: install dependencies, copy `.env`, generate key, migrate database, dan build frontend.

## Development

```bash
composer run dev
```

## API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/api/sensor/data` | ESP32 kirim data sensor |
| `GET` | `/api/sensor/latest` | Data sensor terbaru |
| `GET` | `/api/sensor/history` | Riwayat 50 data terakhir |
| `GET` | `/api/sensor/status` | Status koneksi ESP32 |
| `POST` | `/api/pump/command` | Kirim perintah pompa |
| `GET` | `/api/pump/command/pending` | ESP32 polling perintah |
| `PUT` | `/api/pump/command/{id}/executed` | Konfirmasi eksekusi pompa |

## Lisensi

MIT
