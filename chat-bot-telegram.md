PROJECT BRIEF FINAL

BOT TELEGRAM + INTEGRASI API HFM KOMUNITAS TRADING

━━━━━━━━━━━━━━━━━━━━

TUJUAN

Membangun sistem Bot Telegram yang terintegrasi dengan API HFM untuk mengotomatisasi proses:

• Lead Capture
• Screening Leads
• Registrasi Member Baru
• Ubah IB
• Verifikasi Data HFM
• Validasi Deposit
• Approval Member
• Distribusi Link Komunitas
• Human Take Over Support

Tujuan utama:

• Meminimalisir pekerjaan manual admin
• Mempercepat onboarding member
• Mengurangi human error
• Meningkatkan conversion leads menjadi member aktif
• Mempermudah monitoring seluruh proses member

━━━━━━━━━━━━━━━━━━━━

FLOW 1 — LEADS MASUK

TRIGGER

User mengirim:

• Halo
• Kak
• Join
• Mau Join
• Stiker
• Chat Pertama

BOT

Halo Kak 👋

Terima kasih sudah menghubungi kami.

Saya akan membantu proses bergabung ke komunitas.

Boleh saya tahu nama Kakak?

Status:

LEAD

━━━━━━━━━━━━━━━━━━━━

FLOW 2 — SCREENING LEADS

USER

Mengirim nama.

Contoh:

Andi

━━━━━━━━━━━━━━━━━━━━

BOT

Salam kenal Kak Andi 😊

Sebelum saya bantu proses bergabung, saya ingin mengenal kebutuhan Kakak terlebih dahulu.

Apa tujuan utama Kakak ingin bergabung ke komunitas kami?

Pilihan:

📚 Belajar Trading

🤝 Mencari Komunitas

💰 Menambah Income

📈 Mengembangkan Skill Trading

━━━━━━━━━━━━━━━━━━━━

USER

Memilih tujuan.

━━━━━━━━━━━━━━━━━━━━

BOT

Baik Kak Andi 👍

Kalau boleh tahu, bagaimana pengalaman trading Kakak saat ini?

Pilihan:

🔹 Belum Pernah Trading

🔹 Pemula

🔹 Sudah Trading

🔹 Berpengalaman

━━━━━━━━━━━━━━━━━━━━

DATABASE

Simpan:

• Telegram ID
• Username Telegram
• Nama
• Tujuan Bergabung
• Pengalaman Trading
• Waktu Leads Masuk
• Timestamp Setiap Tahapan

Status:

LEAD_QUALIFIED

━━━━━━━━━━━━━━━━━━━━

FLOW 3 — BENEFIT KOMUNITAS

BOT

Terima kasih Kak Andi 🙏

Komunitas kami menyediakan:

✅ Edukasi lengkap Smart Money Concept (SMC)

✅ Edukasi Support & Resistance (SNR)

✅ Edukasi ICT

✅ Live Mapping & Trading setiap hari

✅ Win Rate hingga 98%

✅ Komunitas aktif 24/7

✅ Full Support jika ada kendala Deposit & Withdrawal

Selanjutnya saya ingin memastikan status akun trading Kakak.

Apakah Kakak sudah memiliki akun HFM?

Pilihan:

[SUDAH PUNYA AKUN HFM]

[BELUM PUNYA AKUN HFM]

━━━━━━━━━━━━━━━━━━━━

FLOW 4A — REGISTRASI BARU

Jika user memilih:

BELUM PUNYA AKUN HFM

BOT

Mengirim:

• PDF Registrasi HFM
• Link Registrasi HFM (tracking referral)

Tombol:

[SAYA SUDAH REGISTRASI]

[ADA KENDALA]

Status:

REGISTER_PENDING

━━━━━━━━━━━━━━━━━━━━

FLOW 4B — UBAH IB

Jika user memilih:

SUDAH PUNYA AKUN HFM

STEP 1

Bot mengirim:

PDF Ubah IB Step 1

Tombol:

[SAYA SUDAH SELESAI STEP 1]

[ADA KENDALA]

Status:

IB_STEP_1_DONE

━━━━━━━━━━━━━━━━━━━━

STEP 2

Hanya dapat diakses setelah Step 1 selesai.

Bot mengirim:

PDF Ubah IB Step 2

Tombol:

[SAYA SUDAH SELESAI STEP 2]

[ADA KENDALA]

Status:

IB_STEP_2_DONE

━━━━━━━━━━━━━━━━━━━━

FLOW 5 — INFORMASI SYARAT JOIN

BOT

Syarat bergabung komunitas:

✅ Akun HFM terdaftar melalui jaringan kami

ATAU

✅ Akun berhasil melakukan Ubah IB

DAN

✅ Deposit aktif minimal $20

Jika saldo kurang dari $20 maka belum dapat bergabung ke komunitas.

Tombol:

[LANJUTKAN]

━━━━━━━━━━━━━━━━━━━━

FLOW 6 — INPUT DATA

BOT

Silakan kirim data berikut:

Nama Lengkap:

ID Wallet HFM:

ID Akun MT5:

Tombol:

[ADA KENDALA]

Status:

WAITING_VERIFICATION

Catatan:

Jika API HFM mampu membaca data secara otomatis, user cukup mengirim Wallet ID dan MT5 ID.

━━━━━━━━━━━━━━━━━━━━

FLOW 7 — INTEGRASI API HFM

Bot melakukan request otomatis ke API HFM.

Data yang diambil:

• Nama Akun
• Wallet ID
• MT5 ID
• Status Akun
• Status IB
• Equity
• Deposit
• Tanggal Registrasi
• Email (jika tersedia)
• Nomor Telepon (jika tersedia)

━━━━━━━━━━━━━━━━━━━━

FLOW 8 — VALIDASI OTOMATIS

VALIDASI 1

Apakah akun ditemukan?

Jika tidak:

STATUS:

DATA_NOT_FOUND

BOT

Data akun tidak ditemukan.

Silakan periksa kembali data yang dikirim.

Tombol:

[ADA KENDALA]

━━━━━━━━━━━━━━━━━━━━

VALIDASI 2

Apakah akun berada di jaringan IB komunitas?

Jika tidak:

STATUS:

IB_NOT_MATCH

BOT

Akun belum berada di jaringan IB komunitas.

Silakan lakukan proses Ubah IB terlebih dahulu.

Tombol:

[ADA KENDALA]

━━━━━━━━━━━━━━━━━━━━

VALIDASI 3

Apakah deposit minimal $20?

Jika tidak:

STATUS:

WAITING_DEPOSIT

BOT

Saldo akun saat ini belum memenuhi syarat minimum bergabung komunitas yaitu $20.

Silakan lakukan deposit minimal $20 untuk melanjutkan proses bergabung.

Tombol:

[CEK ULANG SALDO]

[ADA KENDALA]

━━━━━━━━━━━━━━━━━━━━

VALIDASI BERHASIL

Jika:

✅ Akun ditemukan

✅ IB sesuai

✅ Deposit minimal $20

STATUS:

VERIFIED

━━━━━━━━━━━━━━━━━━━━

FLOW 9 — NOTIFIKASI ADMIN

Bot mengirim ke Grup Admin:

🟢 MEMBER VERIFIED

Nama:

Telegram:

Wallet ID:

MT5 ID:

Equity:

Tanggal Registrasi:

Status:

SIAP APPROVE

Tombol:

[APPROVE]

[PENDING]

[REJECT]

━━━━━━━━━━━━━━━━━━━━

FLOW 10 — APPROVE MEMBER

Jika Admin klik APPROVE

Bot otomatis:

1. Status berubah menjadi ACTIVE
2. Menyimpan data ke database member
3. Mengirim link komunitas

BOT KE USER

🎉 Selamat Kak.

Data Kakak telah berhasil diverifikasi.

Silakan bergabung ke komunitas melalui link berikut:

{LINK KOMUNITAS}

Mohon membaca peraturan grup sebelum memulai aktivitas.

Selamat bergabung 🚀

━━━━━━━━━━━━━━━━━━━━

FLOW 11 — PENDING MEMBER

Jika Admin klik PENDING

STATUS:

PENDING_REVIEW

Admin dapat menambahkan catatan.

━━━━━━━━━━━━━━━━━━━━

FLOW 12 — REJECT MEMBER

Jika Admin klik REJECT

STATUS:

REJECTED

Admin wajib memberikan alasan penolakan.

━━━━━━━━━━━━━━━━━━━━

FLOW 13 — BANTUAN KENDALA (HUMAN TAKE OVER)

Flow ini dapat diakses kapan saja selama proses registrasi, ubah IB, verifikasi data, maupun deposit.

━━━━━━━━━━━━━━━━━━━━

TRIGGER

User menekan:

[ADA KENDALA]

Atau mengirim:

• Kendala
• Error
• Gagal
• Tidak Bisa
• Bingung
• Help
• Bantuan

━━━━━━━━━━━━━━━━━━━━

BOT

Mohon pilih kendala yang sedang dialami.

Pilihan:

1. Tidak Bisa Registrasi
2. Tidak Bisa Ubah IB
3. Belum Menerima Email
4. Akun Ditolak
5. Tidak Bisa Login
6. Deposit Belum Masuk
7. Data Tidak Terbaca
8. Kendala Lainnya

━━━━━━━━━━━━━━━━━━━━

STATUS

NEED_HELP_REGISTER

NEED_HELP_IB

NEED_HELP_DEPOSIT

NEED_HELP_DATA

NEED_HELP_OTHER

━━━━━━━━━━━━━━━━━━━━

BOT

Mohon kirim screenshot atau jelaskan kendala yang sedang dialami agar tim kami dapat membantu.

━━━━━━━━━━━━━━━━━━━━

SETELAH USER MENGIRIM SCREENSHOT

Bot otomatis mengirim ke grup admin:

🟡 MEMBER MEMBUTUHKAN BANTUAN

Nama:

Telegram ID:

Status Saat Ini:

Jenis Kendala:

Pesan User:

Lampiran:

Waktu Laporan:

━━━━━━━━━━━━━━━━━━━━

TOMBOL ADMIN

[AMBIL CASE]

[PENDING]

[CLOSE CASE]

━━━━━━━━━━━━━━━━━━━━

JIKA ADMIN MENEKAN

[AMBIL CASE]

STATUS:

UNDER_REVIEW

BOT KE USER

Terima kasih Kak 🙏

Kendala Kakak sudah diterima oleh tim kami dan sedang ditangani.

Mohon menunggu sebentar, admin akan membantu secepatnya.

━━━━━━━━━━━━━━━━━━━━

FITUR HUMAN TAKE OVER

Saat case diambil:

• Bot berhenti mengirim pesan otomatis sementara
• Admin dapat membalas langsung ke user
• Semua percakapan tersimpan di database
• Semua aktivitas admin tercatat pada audit log

━━━━━━━━━━━━━━━━━━━━

JIKA MASALAH SELESAI

Admin menekan:

[LANJUTKAN PROSES]

Bot otomatis mengembalikan user ke tahapan terakhir sebelum mengalami kendala.

Contoh:

Jika user berhenti di Ubah IB Step 2 maka sistem mengembalikan user ke Ubah IB Step 2.

STATUS:

CASE_CLOSED

━━━━━━━━━━━━━━━━━━━━

DASHBOARD ADMIN

Menu yang dibutuhkan:

• Leads Baru
• Leads Qualified
• Registrasi Baru
• Ubah IB Step 1
• Ubah IB Step 2
• Menunggu Deposit
• Data Tidak Ditemukan
• IB Belum Sesuai
• Menunggu Verifikasi
• Verified
• Approved
• Rejected
• Member Aktif
• Need Help
• Under Review
• Case Closed

━━━━━━━━━━━━━━━━━━━━

FITUR TAMBAHAN

1. Follow Up Otomatis 30 Menit

Jika user berhenti di tengah proses.

━━━━━━━━━━━━━━━━━━━━

2. Follow Up Otomatis 24 Jam

Jika registrasi atau ubah IB belum selesai.

━━━━━━━━━━━━━━━━━━━━

3. Auto Recheck API HFM

Khusus status:

WAITING_DEPOSIT

Sistem melakukan pengecekan otomatis setiap 5 menit.

Jika saldo sudah ≥ $20:

STATUS → VERIFIED

Dan otomatis mengirim notifikasi ke grup admin.

━━━━━━━━━━━━━━━━━━━━

4. Riwayat Aktivitas

Menyimpan seluruh perjalanan user dari awal hingga menjadi member aktif.

━━━━━━━━━━━━━━━━━━━━

5. Timestamp

Mencatat waktu pada setiap tahapan.

━━━━━━━━━━━━━━━━━━━━

6. Admin Notes

Admin dapat menambahkan catatan pada setiap leads atau member.

━━━━━━━━━━━━━━━━━━━━

7. Export Data

Format:

• Excel
• CSV
• Google Spreadsheet

━━━━━━━━━━━━━━━━━━━━

8. Audit Log

Mencatat seluruh aktivitas admin:

• Approve
• Reject
• Pending
• Ambil Case
• Close Case
• Catatan Admin

━━━━━━━━━━━━━━━━━━━━

ROLE & HAK AKSES

OWNER

• Full Access
• Kelola Admin
• Melihat Semua Data
• Export Data

HEAD ADMIN

• Approve Member
• Reject Member
• Ambil Case
• Monitoring Dashboard

ADMIN

• Follow Up Leads
• Membantu Kendala
• Menambahkan Notes
• Monitoring Data

━━━━━━━━━━━━━━━━━━━━

DATABASE STATUS

LEAD

LEAD_QUALIFIED

REGISTER_PENDING

IB_STEP_1_DONE

IB_STEP_2_DONE

WAITING_VERIFICATION

DATA_NOT_FOUND

IB_NOT_MATCH

WAITING_DEPOSIT

VERIFIED

PENDING_REVIEW

APPROVED

REJECTED

ACTIVE

NEED_HELP_REGISTER

NEED_HELP_IB

NEED_HELP_DEPOSIT

NEED_HELP_DATA

NEED_HELP_OTHER

UNDER_REVIEW

CASE_CLOSED

━━━━━━━━━━━━━━━━━━━━

TARGET FLOW AKHIR

Leads Masuk

↓

Bot Screening

↓

Registrasi / Ubah IB

↓

Input Data

↓

API HFM Check

↓

Validasi IB

↓

Validasi Deposit Minimum $20

↓

Verified

↓

Approve Admin

↓

Link Komunitas Dikirim

↓

Member Aktif

