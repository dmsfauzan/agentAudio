# AudioAgent — YouTube to MP3 & TikTok Downloader Tanpa Watermark

> Satu tool untuk dua platform. YouTube → MP3 320kbps. TikTok → MP4 tanpa watermark hingga 1080p. Gratis, tanpa iklan, tanpa login.

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://php.net)
[![yt-dlp](https://img.shields.io/badge/yt--dlp-latest-brightgreen)](https://github.com/yt-dlp/yt-dlp)
[![FFmpeg](https://img.shields.io/badge/FFmpeg-6.x-darkgreen)](https://ffmpeg.org)
[![Midtrans](https://img.shields.io/badge/Midtrans-Snap-orange)](https://midtrans.com)
[![License](https://img.shields.io/badge/license-MIT-blue)](#lisensi)

**Live Demo:** `http://localhost/AudioAgent` (Laragon) • **Repo:** https://github.com/dmsfauzan/agentAudio

---

## ✨ Fitur Utama

| Fitur | Detail |
|-------|--------|
| **YouTube → Audio** | MP3 320kbps, M4A 256kbps, OPUS 160kbps, WAV lossless, FLAC |
| **YouTube → Video** | MP4 merge `bv*+ba` hingga 2160p (4K), pilih 360p/480p/720p/1080p |
| **TikTok → MP4** | Tanpa watermark (`play_addr` + `--xff US`), pilih 540p / 720p / 1080p / Auto Best |
| **Trim / Potong** | `download-sections "*00:00-01:30"` — potong durasi tanpa re-encode berlebih |
| **Playlist (ZIP)** | `api/playlist.php` — download ZIP max 10 item |
| **Cache** | `lib/cache.php` — cache file by hash URL+quality+format+trim, hit via `X-Cache: HIT` |
| **Async Job + Worker** | `api/job.php` + `worker.php` (slot lock, `WORKER_MAX=3`, progress polling) |
| **PWA** | `manifest.json` + `sw.js` — standalone, offline cache |
| **Traktir / Donasi** | Midtrans Snap — VA BCA/BNI/BRI, QRIS, GoPay/OVO/DANA → `data/donations.json` + Hall of Fame |
| **SEO** | Landing `youtube-to-mp3.php`, `tiktok-downloader.php`, `sitemap.xml`, `robots.txt`, JSON-LD |
| **Rate Limit & Logger** | `lib/ratelimit.php`, `lib/logger.php` → `data/downloads.log` |

---

## 📸 Tampilan

- **Hero centered** (ala YTMP3/gmpion) — platform toggle YouTube/TikTok, format selector dinamis, preview thumbnail + durasi.
- **Cara Pakai 3 langkah** — Tempel link → Preview & pilih kualitas → Download.
- **Hall of Fame** — daftar donatur `settlement` dari `lib/db.php:get_settled_donations()`.
- Dark/Light toggle (`localStorage`), i18n ID/EN, history `localStorage`.

---

## 🧱 Tech Stack

- **Backend:** PHP 8.3 (tanpa framework), `yt-dlp.exe` + `ffmpeg`/`ffprobe`/`ffplay`
- **Frontend:** Vanilla JS (`assets/js/main.js`), CSS custom (`assets/css/style.css`), Inter font
- **Payment:** Midtrans Snap (`lib/midtrans.php`, `webhook/midtrans.php`)
- **Queue:** File-based jobs di `data/jobs/` + `data/jobs_out/` (`lib/jobs.php`)
- **Data:** JSON flat-file (`data/donations.json`, `data/stats.json`, `data/ratelimit.json`) — mudah migrasi ke DB

---

## 📁 Struktur Project

```
AudioAgent/
├── index.php                 # Landing utama + tool card + Hall of Fame
├── youtube-to-mp3.php        # SEO landing YouTube
├── tiktok-downloader.php     # SEO landing TikTok
├── config.php                # BASE_URL, Midtrans keys, JOBS_DIR, WORKER_MAX, JOB_TTL
├── worker.php                # CLI worker (loop / --once) — proc_open yt-dlp + progress parse
├── manifest.json / sw.js     # PWA
├── about.php / contact.php / terms.php / privacy.php / dmca.php
├── assets/
│   ├── css/style.css
│   └── js/main.js            # fetch /info, /download, /job, Midtrans Snap, history, theme
├── api/
│   ├── info.php              # POST {url} → metadata (yt-dlp --dump-single-json)
│   ├── download.php          # Sync download (cache, trim, stats, ratelimit)
│   ├── job.php               # Async: POST create → {job_id}, GET?action=status&id=
│   ├── file.php              # Serve file via signed URL (JOB_SECRET HMAC)
│   ├── playlist.php          # ZIP playlist
│   ├── download.php, donations.php, traktir.php, traktir-status.php
├── lib/
│   ├── db.php, cache.php, jobs.php, logger.php, ratelimit.php, stats.php, midtrans.php
├── vendor/
│   ├── yt-dlp.exe            # < 100MB — ikut ter-push
│   └── ffmpeg/               # ffmpeg.exe, ffprobe.exe, ffplay.exe — DI-IGNORE (lihat bawah)
├── data/                     # donations.json, stats.json, jobs/, cache/, downloads.log
├── temp/                     # output sementara (auto-hapus >1 jam)
└── webhook/midtrans.php      # Notifikasi Midtrans → update donations.json
```

---

## 🚀 Instalasi (Laragon / XAMPP / Hosting)

### 1. Clone

```bash
git clone https://github.com/dmsfauzan/agentAudio.git
# atau folder sudah ada di C:\laragon\www\AudioAgent
```

### 2. FFmpeg & yt-dlp

> **Catatan GitHub:** `vendor/ffmpeg/*.exe` di-ignore via `.gitignore` karena melebihi limit 100 MB/file (GitHub menolak push). File tetap ada lokal, tidak ikut ter-push.

**Opsi A — Manual (Windows):**
1. Download FFmpeg build: https://ffmpeg.org/download.html (atau https://github.com/BtbN/FFmpeg-Builds)
2. Extract `ffmpeg.exe`, `ffprobe.exe`, `ffplay.exe` ke `vendor/ffmpeg/`
3. Download `yt-dlp.exe` terbaru: https://github.com/yt-dlp/yt-dlp/releases → simpan ke `vendor/yt-dlp.exe`
4. Cek versi:
```powershell
.\vendor\ffmpeg\ffmpeg.exe -version
.\vendor\yt-dlp.exe --version
```

**Opsi B — Git LFS (jika ingin push binary):**
```bash
git lfs install
git lfs track "vendor/ffmpeg/*.exe"
git add .gitattributes vendor/ffmpeg/*.exe
git commit -m "track ffmpeg via LFS"
git push
```

### 3. Permissions & Config

```powershell
# Pastikan folder writable (Windows: klik kanan → Properties → uncheck Read-only)
# Linux/hosting:
chmod -R 777 data temp data/jobs data/jobs_out data/cache
```

Edit `config.php`:

```php
define('BASE_URL', 'https://domainkamu.com'); // ganti dari localhost/AudioAgent
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-xxxxx');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-xxxxx');
define('MIDTRANS_IS_PRODUCTION', false); // true untuk production
define('JOB_SECRET', 'ganti-secret-random-32char');
```

Atau via environment variable (lebih aman di hosting):

```bash
MIDTRANS_SERVER_KEY=SB-Mid-server-xxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxx
JOB_SECRET=xxx
```

### 4. Jalankan

- **Laragon:** Buka `http://localhost/AudioAgent/` (atau klik Start → Apache/Nginx)
- **PHP built-in:**

```bash
php -S localhost:8000 -t C:\laragon\www\AudioAgent
# buka http://localhost:8000
```

### 5. Worker Async (opsional tapi direkomendasikan)

Sync `api/download.php` jalan tanpa worker. Untuk job async `api/job.php`:

```bash
# loop terus
php worker.php

# one-shot (dipanggil otomatis oleh job.php via popen/nohup)
php worker.php --once

# Windows Task Scheduler / Linux cron — jaga worker tetap hidup
# Linux systemd / pm2 contoh:
# pm2 start worker.php --name audioagent-worker --interpreter php
```

`WORKER_MAX` (default 3) = max parallel job via slot lock `data/jobs/slot.*.lock`.

---

## ⚙️ Konfigurasi

| Key di `config.php` | Default | Deskripsi |
|---------------------|---------|-----------|
| `BASE_URL` | `http://localhost/AudioAgent` | Canonical URL untuk SEO |
| `MIDTRANS_SERVER_KEY` / `CLIENT_KEY` | `SB-Mid-server/client-xxx` | Dari dashboard Midtrans Sandbox/Production |
| `MIDTRANS_IS_PRODUCTION` | `false` | `false`=sandbox, `true`=production |
| `TRAKTIR_MIN` / `TRAKTIR_MAX` | `1000` / `10000000` | Range donasi |
| `JOBS_DIR` / `JOBS_OUT_DIR` | `data/jobs` | Queue & hasil worker |
| `JOB_SECRET` | `audioagent-secret-change-me` | HMAC untuk `job_signed_url()` |
| `WORKER_MAX` | `3` | Concurrency |
| `JOB_TTL` | `3600` | Expiry file hasil (detik) |

---

## 🔌 API Reference

### POST `api/info.php` — Ambil Metadata

```bash
curl -X POST http://localhost/AudioAgent/api/info.php \
  -H "Content-Type: application/json" \
  -d '{"url":"https://www.youtube.com/watch?v=dQw4w9WgXcQ"}'
```

**Response sukses:**
```json
{
  "success": true,
  "data": {
    "id": "dQw4w9WgXcQ",
    "platform": "youtube",
    "title": "Rick Astley - Never Gonna Give You Up",
    "uploader": "Rick Astley",
    "duration": 213,
    "duration_string": "03:33",
    "thumbnail": "https://i.ytimg.com/vi/.../hqdefault.jpg",
    "view_count": 123456,
    "qualities": [],
    "video_qualities": [360,480,720,1080],
    "is_slideshow": false
  }
}
```
TikTok `qualities` berisi `[540,720,1080]` (tanpa watermark).

### POST `api/download.php` — Download Sync

```bash
# YouTube audio MP3
curl -X POST http://localhost/AudioAgent/api/download.php \
  -H "Content-Type: application/json" \
  -d '{"url":"https://youtu.be/dQw4w9WgXcQ","format":"mp3","quality":"0","mode":"audio"}' --output out.mp3

# TikTok 720p
curl -X POST http://localhost/AudioAgent/api/download.php \
  -d '{"url":"https://www.tiktok.com/@scout2015/video/6718335390845095173","platform":"tiktok","quality":"720"}' --output tt.mp4

# Trim
curl -X POST http://localhost/AudioAgent/api/download.php \
  -d '{"url":"https://youtu.be/...","format":"mp3","trim_start":"00:10","trim_end":"01:30"}' --output trim.mp3
```

**Header penting:** `X-Cache: HIT` jika dari cache, `X-File-Name`, `Content-Disposition: attachment`.

### POST `api/job.php` — Async Job

```bash
# Create
curl -X POST "http://localhost/AudioAgent/api/job.php" \
  -H "Content-Type: application/json" \
  -d '{"url":"https://youtu.be/dQw4w9WgXcQ","mode":"audio","format":"mp3"}'
# → {"success":true,"job_id":"a1b2c3..."}

# Status polling
curl "http://localhost/AudioAgent/api/job.php?action=status&id=a1b2c3"
# → {"success":true,"data":{"status":"done","progress":100,"download_url":"api/file.php?id=...&sig=..."}}

# Download hasil
curl "http://localhost/AudioAgent/api/file.php?id=a1b2c3&sig=..." --output out.mp3
```

### Lainnya

- `GET api/donations.php` — list donatur Hall of Fame
- `POST api/traktir.php` → create Snap token, `POST api/traktir-status.php` → cek status
- `POST api/playlist.php` → ZIP (max 10 URL)

---

## ☕ Traktir / Midtrans

1. Daftar di https://dashboard.sandbox.midtrans.com (sandbox) / https://dashboard.midtrans.com (production)
2. Ambil **Server Key** & **Client Key** → isi di `config.php` atau env
3. Set **Payment Notification URL** di dashboard ke `https://domainkamu.com/webhook/midtrans.php`
4. Test: klik **☕ Traktir** di homepage → Snap popup → pilih VA/QRIS/e-wallet → bayar → webhook update `data/donations.json` → tampil di Hall of Fame

Untuk Snap di frontend, `index.php:41-44` load Snap.js hanya jika `MIDTRANS_CLIENT_KEY` bukan placeholder.

---

## 🌐 SEO & Deploy

- Ganti `BASE_URL` di `config.php` + `sitemap.xml` loc ke domain production.
- `robots.txt` & `sitemap.xml` sudah ada.
- Pastikan `data/` & `temp/` tidak listing (sudah ada `.htaccess` deny).
- Cron pembersih `temp/` & `data/cache/` sudah jalan di `download.php` (hapus >1 jam) + `worker.php:cleanup_jobs()`.

---

## ⚠️ Legal / DMCA

Gunakan hanya untuk konten yang kamu punya haknya (upload sendiri, free-to-share, atau fair use pribadi). Jangan menyebarkan konten berhak cipta tanpa izin. Lihat `terms.php`, `privacy.php`, `dmca.php`.

`yt-dlp` & `FFmpeg` adalah project open-source terpisah — patuhi lisensi masing-masing.

---

## 📝 Lisensi

MIT — bebas pakai, modifikasi, self-host. Traktir bersifat sukarela.

---

## 🙏 Kredit

- [yt-dlp](https://github.com/yt-dlp/yt-dlp) — extractor
- [FFmpeg](https://ffmpeg.org) — mux/transcode
- [Midtrans](https://midtrans.com) — payment gateway
- Dibuat dengan ❤️ untuk kemudahan download pribadi
