<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\BotSetting;
use App\Models\Lead;
use App\Models\SupportCase;
use Illuminate\Support\Facades\Storage;

class ConversationHandler
{
    private TelegramService $telegram;
    private HfmApiService $hfm;
    private BotSetting $settings;

    private const GREETING_TRIGGERS = ['halo', 'kak', 'join', 'mau join', 'hi', 'hello'];
    private const HELP_TRIGGERS = ['kendala', 'error', 'gagal', 'tidak bisa', 'bingung', 'help', 'bantuan'];

    public function __construct()
    {
        $this->settings = BotSetting::current();
        $this->telegram = TelegramService::make();
        $this->hfm = HfmApiService::make();
    }

    public function handle(array $update): void
    {
        if (! $this->settings->is_active) {
            return;
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);

            return;
        }

        if (! isset($update['message'])) {
            return;
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');
        $user = $message['from'];

        $lead = Lead::firstOrCreate(
            ['telegram_id' => $user['id']],
            [
                'telegram_username' => $user['username'] ?? null,
                'status' => LeadStatus::Lead,
                'conversation_step' => 'awaiting_name',
                'last_activity_at' => now(),
            ]
        );

        if ($lead->wasRecentlyCreated) {
            $lead->logActivity('lead_created', null, LeadStatus::Lead->value);
        }

        if ($lead->bot_paused) {
            return;
        }

        if ($this->isHelpTrigger($text)) {
            $this->startHelpFlow($lead, $chatId);

            return;
        }

        if ($lead->status === LeadStatus::Lead && $this->isGreeting($text, $message)) {
            $this->sendWelcome($lead, $chatId);

            return;
        }

        match ($lead->conversation_step) {
            'awaiting_name' => $this->handleName($lead, $chatId, $text),
            'awaiting_purpose' => $this->handlePurpose($lead, $chatId, $text),
            'awaiting_experience' => $this->handleExperience($lead, $chatId, $text),
            'awaiting_hfm_status' => null,
            'awaiting_account_data' => $this->handleAccountData($lead, $chatId, $text),
            'awaiting_help_detail' => $this->handleHelpDetail($lead, $chatId, $text, $message),
            default => $this->handleByStatus($lead, $chatId, $text),
        };
    }

    private function handleCallback(array $callback): void
    {
        $data = $callback['data'];
        $chatId = $callback['message']['chat']['id'];
        $userId = $callback['from']['id'];

        $this->telegram->answerCallbackQuery($callback['id']);

        $lead = Lead::where('telegram_id', $userId)->first();
        if (! $lead || $lead->bot_paused) {
            return;
        }

        if (str_starts_with($data, 'admin_')) {
            $this->handleAdminAction($data, $lead, $callback);

            return;
        }

        match ($data) {
            'purpose_learn' => $this->savePurpose($lead, $chatId, 'Belajar Trading'),
            'purpose_community' => $this->savePurpose($lead, $chatId, 'Mencari Komunitas'),
            'purpose_income' => $this->savePurpose($lead, $chatId, 'Menambah Income'),
            'purpose_skill' => $this->savePurpose($lead, $chatId, 'Mengembangkan Skill Trading'),
            'exp_beginner' => $this->saveExperience($lead, $chatId, 'Belum Pernah Trading'),
            'exp_newbie' => $this->saveExperience($lead, $chatId, 'Pemula'),
            'exp_trading' => $this->saveExperience($lead, $chatId, 'Sudah Trading'),
            'exp_expert' => $this->saveExperience($lead, $chatId, 'Berpengalaman'),
            'has_hfm' => $this->startIbFlow($lead, $chatId),
            'no_hfm' => $this->startRegistrationFlow($lead, $chatId),
            'reg_done' => $this->afterRegistration($lead, $chatId),
            'ib_step1_done' => $this->ibStep1Done($lead, $chatId),
            'ib_step2_done' => $this->ibStep2Done($lead, $chatId),
            'continue_join' => $this->askAccountData($lead, $chatId),
            'recheck_balance' => $this->verifyHfmAccount($lead, $chatId),
            'need_help' => $this->startHelpFlow($lead, $chatId),
            'help_reg' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpRegister, 'Tidak Bisa Registrasi'),
            'help_ib' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpIb, 'Tidak Bisa Ubah IB'),
            'help_email' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpOther, 'Belum Menerima Email'),
            'help_rejected' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpOther, 'Akun Ditolak'),
            'help_login' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpOther, 'Tidak Bisa Login'),
            'help_deposit' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpDeposit, 'Deposit Belum Masuk'),
            'help_data' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpData, 'Data Tidak Terbaca'),
            'help_other' => $this->selectHelpType($lead, $chatId, LeadStatus::NeedHelpOther, 'Kendala Lainnya'),
            default => null,
        };
    }

    private function isGreeting(string $text, array $message): bool
    {
        if (isset($message['sticker'])) {
            return true;
        }

        $lower = strtolower($text);

        foreach (self::GREETING_TRIGGERS as $trigger) {
            if (str_contains($lower, $trigger)) {
                return true;
            }
        }

        return false;
    }

    private function isHelpTrigger(string $text): bool
    {
        $lower = strtolower($text);

        foreach (self::HELP_TRIGGERS as $trigger) {
            if (str_contains($lower, $trigger)) {
                return true;
            }
        }

        return false;
    }

    private function sendWelcome(Lead $lead, int $chatId): void
    {
        $lead->update(['conversation_step' => 'awaiting_name', 'last_activity_at' => now()]);
        $this->telegram->sendMessage($chatId, $this->settings->welcome_message ?? 'Halo! Boleh saya tahu nama Kakak?');
    }

    private function handleName(Lead $lead, int $chatId, string $text): void
    {
        if (strlen($text) < 2) {
            $this->telegram->sendMessage($chatId, 'Mohon kirim nama yang valid.');

            return;
        }

        $lead->update(['name' => $text, 'conversation_step' => 'awaiting_purpose']);
        $this->telegram->sendMessage(
            $chatId,
            "Salam kenal Kak {$text} 😊\n\nSebelum saya bantu proses bergabung, saya ingin mengenal kebutuhan Kakak terlebih dahulu.\n\nApa tujuan utama Kakak ingin bergabung ke komunitas kami?",
            TelegramService::inlineKeyboard([
                [TelegramService::button('📚 Belajar Trading', 'purpose_learn')],
                [TelegramService::button('🤝 Mencari Komunitas', 'purpose_community')],
                [TelegramService::button('💰 Menambah Income', 'purpose_income')],
                [TelegramService::button('📈 Mengembangkan Skill Trading', 'purpose_skill')],
            ])
        );
    }

    private function savePurpose(Lead $lead, int $chatId, string $purpose): void
    {
        $lead->update(['join_purpose' => $purpose, 'conversation_step' => 'awaiting_experience']);
        $this->telegram->sendMessage(
            $chatId,
            "Baik Kak {$lead->name} 👍\n\nKalau boleh tahu, bagaimana pengalaman trading Kakak saat ini?",
            TelegramService::inlineKeyboard([
                [TelegramService::button('🔹 Belum Pernah Trading', 'exp_beginner')],
                [TelegramService::button('🔹 Pemula', 'exp_newbie')],
                [TelegramService::button('🔹 Sudah Trading', 'exp_trading')],
                [TelegramService::button('🔹 Berpengalaman', 'exp_expert')],
            ])
        );
    }

    private function handlePurpose(Lead $lead, int $chatId, string $text): void
    {
        $this->telegram->sendMessage($chatId, 'Silakan pilih tujuan bergabung menggunakan tombol di bawah.');
    }

    private function saveExperience(Lead $lead, int $chatId, string $experience): void
    {
        $lead->update(['trading_experience' => $experience]);
        $lead->transitionTo(LeadStatus::LeadQualified, 'screening_completed');

        $this->telegram->sendMessage(
            $chatId,
            "Terima kasih Kak {$lead->name} 🙏\n\nKomunitas kami menyediakan:\n\n✅ Edukasi lengkap Smart Money Concept (SMC)\n✅ Edukasi Support & Resistance (SNR)\n✅ Edukasi ICT\n✅ Live Mapping & Trading setiap hari\n✅ Win Rate hingga 98%\n✅ Komunitas aktif 24/7\n✅ Full Support jika ada kendala Deposit & Withdrawal\n\nSelanjutnya saya ingin memastikan status akun trading Kakak.\n\nApakah Kakak sudah memiliki akun HFM?",
            TelegramService::inlineKeyboard([
                [
                    TelegramService::button('SUDAH PUNYA AKUN HFM', 'has_hfm'),
                    TelegramService::button('BELUM PUNYA AKUN HFM', 'no_hfm'),
                ],
            ])
        );
        $lead->update(['conversation_step' => 'awaiting_hfm_status']);
    }

    private function handleExperience(Lead $lead, int $chatId, string $text): void
    {
        $this->telegram->sendMessage($chatId, 'Silakan pilih pengalaman trading menggunakan tombol di bawah.');
    }

    private function startRegistrationFlow(Lead $lead, int $chatId): void
    {
        $lead->update(['has_hfm_account' => false]);
        $lead->transitionTo(LeadStatus::RegisterPending, 'registration_started');

        $buttons = [
            [TelegramService::button('SAYA SUDAH REGISTRASI', 'reg_done')],
            [TelegramService::button('ADA KENDALA', 'need_help')],
        ];

        if ($this->settings->hfm_referral_link) {
            array_unshift($buttons, [TelegramService::urlButton('🔗 Link Registrasi HFM', $this->settings->hfm_referral_link)]);
        }

        $this->sendPdfIfExists($chatId, $this->settings->pdf_registration, 'Panduan Registrasi HFM');
        $this->telegram->sendMessage(
            $chatId,
            "Silakan lakukan registrasi akun HFM melalui link referral kami.\n\nSetelah selesai registrasi, tekan tombol di bawah.",
            TelegramService::inlineKeyboard($buttons)
        );
    }

    private function startIbFlow(Lead $lead, int $chatId): void
    {
        $lead->update(['has_hfm_account' => true]);
        $this->sendPdfIfExists($chatId, $this->settings->pdf_ib_step1, 'Panduan Ubah IB - Step 1');
        $this->telegram->sendMessage(
            $chatId,
            "Silakan ikuti panduan Ubah IB Step 1.\n\nSetelah selesai, tekan tombol di bawah.",
            TelegramService::inlineKeyboard([
                [TelegramService::button('SAYA SUDAH SELESAI STEP 1', 'ib_step1_done')],
                [TelegramService::button('ADA KENDALA', 'need_help')],
            ])
        );
    }

    private function ibStep1Done(Lead $lead, int $chatId): void
    {
        $lead->transitionTo(LeadStatus::IbStep1Done, 'ib_step1_completed');
        $this->sendPdfIfExists($chatId, $this->settings->pdf_ib_step2, 'Panduan Ubah IB - Step 2');
        $this->telegram->sendMessage(
            $chatId,
            "Lanjut ke Step 2 Ubah IB.\n\nSetelah selesai, tekan tombol di bawah.",
            TelegramService::inlineKeyboard([
                [TelegramService::button('SAYA SUDAH SELESAI STEP 2', 'ib_step2_done')],
                [TelegramService::button('ADA KENDALA', 'need_help')],
            ])
        );
    }

    private function ibStep2Done(Lead $lead, int $chatId): void
    {
        $lead->transitionTo(LeadStatus::IbStep2Done, 'ib_step2_completed');
        $this->showJoinRequirements($lead, $chatId);
    }

    private function afterRegistration(Lead $lead, int $chatId): void
    {
        $this->showJoinRequirements($lead, $chatId);
    }

    private function showJoinRequirements(Lead $lead, int $chatId): void
    {
        $min = $this->settings->min_deposit;
        $this->telegram->sendMessage(
            $chatId,
            "Syarat bergabung komunitas:\n\n✅ Akun HFM terdaftar melalui jaringan kami\nATAU\n✅ Akun berhasil melakukan Ubah IB\n\nDAN\n\n✅ Deposit aktif minimal \${$min}\n\nJika saldo kurang dari \${$min} maka belum dapat bergabung ke komunitas.",
            TelegramService::inlineKeyboard([
                [TelegramService::button('LANJUTKAN', 'continue_join')],
                [TelegramService::button('ADA KENDALA', 'need_help')],
            ])
        );
    }

    private function askAccountData(Lead $lead, int $chatId): void
    {
        $lead->update(['conversation_step' => 'awaiting_account_data']);
        $lead->transitionTo(LeadStatus::WaitingVerification, 'awaiting_account_data');
        $this->telegram->sendMessage(
            $chatId,
            "Silakan kirim data berikut dalam satu pesan:\n\nNama Lengkap:\nID Wallet HFM:\nID Akun MT5:\n\nContoh:\nNama Lengkap: Andi Pratama\nID Wallet HFM: 12345678\nID Akun MT5: 87654321",
            TelegramService::inlineKeyboard([
                [TelegramService::button('ADA KENDALA', 'need_help')],
            ])
        );
    }

    private function handleAccountData(Lead $lead, int $chatId, string $text): void
    {
        $parsed = $this->parseAccountData($text);

        if (! $parsed['wallet_id'] || ! $parsed['mt5_id']) {
            $this->telegram->sendMessage($chatId, 'Format data belum lengkap. Mohon kirim Nama Lengkap, ID Wallet HFM, dan ID Akun MT5.');

            return;
        }

        $lead->update([
            'full_name' => $parsed['full_name'] ?? $lead->name,
            'wallet_id' => $parsed['wallet_id'],
            'mt5_id' => $parsed['mt5_id'],
        ]);

        $this->verifyHfmAccount($lead, $chatId);
    }

    private function parseAccountData(string $text): array
    {
        $result = ['full_name' => null, 'wallet_id' => null, 'mt5_id' => null];

        if (preg_match('/nama\s*lengkap\s*[:：]?\s*(.+)/i', $text, $m)) {
            $result['full_name'] = trim($m[1]);
        }
        if (preg_match('/wallet\s*(?:id|hfm)?\s*[:：]?\s*(\d+)/i', $text, $m)) {
            $result['wallet_id'] = trim($m[1]);
        }
        if (preg_match('/mt5\s*(?:id|akun)?\s*[:：]?\s*(\d+)/i', $text, $m)) {
            $result['mt5_id'] = trim($m[1]);
        }

        if (! $result['wallet_id'] && preg_match('/\b(\d{6,})\b/', $text, $m)) {
            $result['wallet_id'] = $m[1];
        }

        return $result;
    }

    public function verifyHfmAccount(Lead $lead, int $chatId): void
    {
        $result = $this->hfm->verifyAccount($lead->wallet_id ?? '', $lead->mt5_id ?? '');

        if (! $result['found']) {
            $lead->transitionTo(LeadStatus::DataNotFound, 'hfm_data_not_found');
            $this->telegram->sendMessage(
                $chatId,
                "Data akun tidak ditemukan.\n\nSilakan periksa kembali data yang dikirim.",
                TelegramService::inlineKeyboard([
                    [TelegramService::button('ADA KENDALA', 'need_help')],
                ])
            );

            return;
        }

        $lead->update([
            'hfm_account_name' => $result['account_name'],
            'hfm_email' => $result['email'] ?? null,
            'hfm_phone' => $result['phone'] ?? null,
            'hfm_ib_status' => $result['ib_status'],
            'hfm_equity' => $result['equity'],
            'hfm_deposit' => $result['deposit'],
            'hfm_registered_at' => $result['registered_at'] ?? null,
        ]);

        if (! $this->hfm->isIbMatch($result['ib_status'])) {
            $lead->transitionTo(LeadStatus::IbNotMatch, 'ib_not_match');
            $this->telegram->sendMessage(
                $chatId,
                "Akun belum berada di jaringan IB komunitas.\n\nSilakan lakukan proses Ubah IB terlebih dahulu.",
                TelegramService::inlineKeyboard([
                    [TelegramService::button('ADA KENDALA', 'need_help')],
                ])
            );

            return;
        }

        if (! $this->hfm->meetsMinDeposit($result['deposit'])) {
            $lead->transitionTo(LeadStatus::WaitingDeposit, 'waiting_deposit');
            $this->telegram->sendMessage(
                $chatId,
                "Saldo akun saat ini belum memenuhi syarat minimum bergabung komunitas yaitu \${$this->settings->min_deposit}.\n\nSilakan lakukan deposit minimal \${$this->settings->min_deposit} untuk melanjutkan proses bergabung.",
                TelegramService::inlineKeyboard([
                    [TelegramService::button('CEK ULANG SALDO', 'recheck_balance')],
                    [TelegramService::button('ADA KENDALA', 'need_help')],
                ])
            );

            return;
        }

        $lead->transitionTo(LeadStatus::Verified, 'hfm_verified');
        $this->notifyAdminForApproval($lead);
        $this->telegram->sendMessage($chatId, "Data Kakak sedang diverifikasi oleh tim admin. Mohon tunggu sebentar 🙏");
    }

    private function notifyAdminForApproval(Lead $lead): void
    {
        if (! $this->settings->admin_group_chat_id) {
            return;
        }

        $text = "🟢 <b>MEMBER VERIFIED</b>\n\n"
            ."Nama: {$lead->full_name}\n"
            ."Telegram: @{$lead->telegram_username} ({$lead->telegram_id})\n"
            ."Wallet ID: {$lead->wallet_id}\n"
            ."MT5 ID: {$lead->mt5_id}\n"
            ."Equity: \${$lead->hfm_equity}\n"
            ."Deposit: \${$lead->hfm_deposit}\n"
            ."Tanggal Registrasi: {$lead->hfm_registered_at}\n\n"
            ."Status: SIAP APPROVE";

        $this->telegram->sendMessage(
            $this->settings->admin_group_chat_id,
            $text,
            TelegramService::inlineKeyboard([
                [
                    TelegramService::button('APPROVE', "admin_approve_{$lead->id}"),
                    TelegramService::button('PENDING', "admin_pending_{$lead->id}"),
                    TelegramService::button('REJECT', "admin_reject_{$lead->id}"),
                ],
            ])
        );
    }

    private function handleAdminAction(string $data, Lead $lead, array $callback): void
    {
        $parts = explode('_', $data);
        $action = $parts[1] ?? '';
        $leadId = (int) ($parts[2] ?? 0);
        $targetLead = Lead::find($leadId);

        if (! $targetLead) {
            return;
        }

        match ($action) {
            'approve' => $this->approveMember($targetLead),
            'pending' => $this->pendingMember($targetLead),
            'reject' => $this->rejectMember($targetLead),
            'takecase' => $this->takeCase($targetLead),
            'closecase' => $this->closeCase($targetLead),
            'resume' => $this->resumeProcess($targetLead),
            default => null,
        };
    }

    public function approveMember(Lead $lead): void
    {
        $lead->transitionTo(LeadStatus::Active, 'admin_approved');
        $link = $this->settings->community_link ?? 'https://t.me/your_community';
        $this->telegram->sendMessage(
            $lead->telegram_id,
            "🎉 Selamat Kak.\n\nData Kakak telah berhasil diverifikasi.\n\nSilakan bergabung ke komunitas melalui link berikut:\n\n{$link}\n\nMohon membaca peraturan grup sebelum memulai aktivitas.\n\nSelamat bergabung 🚀"
        );
    }

    public function pendingMember(Lead $lead): void
    {
        $lead->transitionTo(LeadStatus::PendingReview, 'admin_pending');
    }

    public function rejectMember(Lead $lead): void
    {
        $lead->transitionTo(LeadStatus::Rejected, 'admin_rejected');
        $this->telegram->sendMessage($lead->telegram_id, 'Mohon maaf, pendaftaran Kakak belum dapat disetujui saat ini. Tim kami akan menghubungi jika ada informasi lebih lanjut.');
    }

    private function startHelpFlow(Lead $lead, int $chatId): void
    {
        $lead->update(['previous_status' => $lead->status?->value]);
        $this->telegram->sendMessage(
            $chatId,
            "Mohon pilih kendala yang sedang dialami.",
            TelegramService::inlineKeyboard([
                [TelegramService::button('1. Tidak Bisa Registrasi', 'help_reg')],
                [TelegramService::button('2. Tidak Bisa Ubah IB', 'help_ib')],
                [TelegramService::button('3. Belum Menerima Email', 'help_email')],
                [TelegramService::button('4. Akun Ditolak', 'help_rejected')],
                [TelegramService::button('5. Tidak Bisa Login', 'help_login')],
                [TelegramService::button('6. Deposit Belum Masuk', 'help_deposit')],
                [TelegramService::button('7. Data Tidak Terbaca', 'help_data')],
                [TelegramService::button('8. Kendala Lainnya', 'help_other')],
            ])
        );
    }

    private function selectHelpType(Lead $lead, int $chatId, LeadStatus $status, string $issueType): void
    {
        $lead->transitionTo($status, 'help_requested', $issueType);
        $lead->update(['conversation_step' => 'awaiting_help_detail']);

        SupportCase::create([
            'lead_id' => $lead->id,
            'issue_type' => $issueType,
            'status' => 'open',
        ]);

        $this->telegram->sendMessage($chatId, 'Mohon kirim screenshot atau jelaskan kendala yang sedang dialami agar tim kami dapat membantu.');
    }

    private function handleHelpDetail(Lead $lead, int $chatId, string $text, array $message): void
    {
        $attachmentId = null;
        if (isset($message['photo'])) {
            $photo = end($message['photo']);
            $attachmentId = $photo['file_id'] ?? null;
        }

        $case = $lead->supportCases()->latest()->first();
        if ($case) {
            $case->update([
                'user_message' => $text ?: '[Screenshot]',
                'attachment_file_id' => $attachmentId,
            ]);
        }

        $this->notifyAdminHelp($lead, $text, $attachmentId);
        $lead->update(['conversation_step' => null]);
    }

    private function notifyAdminHelp(Lead $lead, string $message, ?string $attachmentId): void
    {
        if (! $this->settings->admin_group_chat_id) {
            return;
        }

        $text = "🟡 <b>MEMBER MEMBUTUHKAN BANTUAN</b>\n\n"
            ."Nama: {$lead->name}\n"
            ."Telegram ID: {$lead->telegram_id}\n"
            ."Status Saat Ini: {$lead->status?->value}\n"
            ."Pesan User: {$message}\n"
            ."Waktu Laporan: ".now()->format('d/m/Y H:i');

        $this->telegram->sendMessage(
            $this->settings->admin_group_chat_id,
            $text,
            TelegramService::inlineKeyboard([
                [
                    TelegramService::button('AMBIL CASE', "admin_takecase_{$lead->id}"),
                    TelegramService::button('PENDING', "admin_pending_{$lead->id}"),
                    TelegramService::button('CLOSE CASE', "admin_closecase_{$lead->id}"),
                ],
            ])
        );
    }

    public function takeCase(Lead $lead): void
    {
        $lead->update(['bot_paused' => true]);
        $lead->transitionTo(LeadStatus::UnderReview, 'case_taken');
        $this->telegram->sendMessage(
            $lead->telegram_id,
            "Terima kasih Kak 🙏\n\nKendala Kakak sudah diterima oleh tim kami dan sedang ditangani.\n\nMohon menunggu sebentar, admin akan membantu secepatnya."
        );
    }

    public function closeCase(Lead $lead): void
    {
        $lead->update(['bot_paused' => false]);
        $lead->transitionTo(LeadStatus::CaseClosed, 'case_closed');

        if ($lead->previous_status) {
            $this->resumeProcess($lead);
        }
    }

    public function resumeProcess(Lead $lead): void
    {
        $lead->update(['bot_paused' => false]);

        if ($lead->previous_status) {
            $previous = LeadStatus::from($lead->previous_status);
            $lead->transitionTo($previous, 'process_resumed');
        }

        $this->telegram->sendMessage($lead->telegram_id, 'Kendala sudah selesai. Silakan lanjutkan proses bergabung.');
    }

    private function handleByStatus(Lead $lead, int $chatId, string $text): void
    {
        if ($lead->status === LeadStatus::Lead) {
            $this->sendWelcome($lead, $chatId);
        }
    }

    private function sendPdfIfExists(int $chatId, ?string $path, string $caption): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            $this->telegram->sendDocument(
                $chatId,
                Storage::disk('public')->path($path),
                $caption
            );
        }
    }
}
