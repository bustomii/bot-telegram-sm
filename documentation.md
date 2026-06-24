# Bot Telegram HFM - Dokumentasi

Sistem bot Telegram terintegrasi API HFM dengan panel admin Laravel + Inertia + React + Tailwind.

## Stack

- **Backend:** Laravel 12
- **Frontend Admin:** Inertia.js + React + Tailwind CSS
- **Bot:** Telegram Bot API (Webhook)
- **Database:** SQLite (default) / MySQL

## Fitur

- Flow lengkap sesuai brief: Lead capture → Screening → Registrasi/Ubah IB → Verifikasi HFM → Approval Admin → Link Komunitas
- Human Take Over (kendala + pause bot)
- Dashboard monitoring per status
- Konfigurasi bot via panel admin
- Follow-up otomatis (30 menit & 24 jam)
- Auto recheck deposit setiap 5 menit
- Export CSV leads
- Audit log aktivitas admin

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

## Login Default

- Email: `owner@example.com`
- Password: `password`

## Konfigurasi Bot

1. Login ke panel admin
2. Buka menu **Konfigurasi Bot**
3. Isi:
   - **Bot Token** — dari [@BotFather](https://t.me/BotFather)
   - **Webhook Secret** — string acak untuk keamanan
   - **Admin Group Chat ID** — ID grup Telegram admin (format: `-100...`)
   - **Link Komunitas** — link grup setelah approve
   - **HFM Referral Link** — link registrasi referral
   - **HFM API URL & Key** — endpoint API HFM
   - **HFM IB ID** — ID IB komunitas untuk validasi
   - Upload PDF panduan registrasi & ubah IB
4. Klik **Set Webhook Telegram**

## Webhook URL

```
https://domain-anda.com/api/telegram/webhook
```

Pastikan server dapat diakses HTTPS dari internet.

## Scheduler (Follow-up & Recheck Deposit)

Tambahkan ke crontab:

```bash
* * * * * cd /path/to/bot-api && php artisan schedule:run >> /dev/null 2>&1
```

## Struktur Utama

| Path | Keterangan |
|------|------------|
| `app/Services/ConversationHandler.php` | State machine flow bot |
| `app/Services/TelegramService.php` | Wrapper Telegram API |
| `app/Services/HfmApiService.php` | Integrasi API HFM |
| `app/Http/Controllers/Admin/BotConfigController.php` | Panel konfigurasi |
| `resources/js/Pages/Admin/` | Halaman React admin |

## Status Lead

`LEAD` → `LEAD_QUALIFIED` → `REGISTER_PENDING` / `IB_STEP_1_DONE` → `IB_STEP_2_DONE` → `WAITING_VERIFICATION` → `VERIFIED` → `ACTIVE`

Status error: `DATA_NOT_FOUND`, `IB_NOT_MATCH`, `WAITING_DEPOSIT`, `REJECTED`

Status bantuan: `NEED_HELP_*`, `UNDER_REVIEW`, `CASE_CLOSED`

## Catatan HFM API

Jika API HFM belum dikonfigurasi, sistem menggunakan mock response untuk development. Wallet ID `000000` akan dianggap tidak ditemukan.
