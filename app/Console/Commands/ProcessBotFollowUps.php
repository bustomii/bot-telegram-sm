<?php

namespace App\Console\Commands;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\ConversationHandler;
use App\Services\HfmApiService;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class ProcessBotFollowUps extends Command
{
    protected $signature = 'bot:follow-ups';

    protected $description = 'Kirim follow-up otomatis dan recheck deposit HFM';

    public function handle(): void
    {
        $this->followUp30Minutes();
        $this->followUp24Hours();
        $this->recheckDeposits();
    }

    private function followUp30Minutes(): void
    {
        $leads = Lead::whereNull('follow_up_30m_at')
            ->where('last_activity_at', '<=', now()->subMinutes(30))
            ->whereNotIn('status', [LeadStatus::Active, LeadStatus::Rejected, LeadStatus::Verified])
            ->get();

        $telegram = TelegramService::make();

        foreach ($leads as $lead) {
            $telegram->sendMessage(
                $lead->telegram_id,
                "Halo Kak {$lead->name} 👋\n\nKami melihat Kakak belum menyelesaikan proses bergabung. Apakah ada kendala yang bisa kami bantu?"
            );
            $lead->update(['follow_up_30m_at' => now()]);
        }
    }

    private function followUp24Hours(): void
    {
        $statuses = [LeadStatus::RegisterPending, LeadStatus::IbStep1Done];

        $leads = Lead::whereNull('follow_up_24h_at')
            ->whereIn('status', $statuses)
            ->where('last_activity_at', '<=', now()->subHours(24))
            ->get();

        $telegram = TelegramService::make();

        foreach ($leads as $lead) {
            $telegram->sendMessage(
                $lead->telegram_id,
                "Halo Kak {$lead->name} 🙏\n\nProses registrasi/ubah IB belum selesai. Jika ada kendala, tekan tombol ADA KENDALA atau balas pesan ini."
            );
            $lead->update(['follow_up_24h_at' => now()]);
        }
    }

    private function recheckDeposits(): void
    {
        $hfm = HfmApiService::make();
        $handler = app(ConversationHandler::class);

        $leads = Lead::where('status', LeadStatus::WaitingDeposit)->get();

        foreach ($leads as $lead) {
            $result = $hfm->verifyAccount($lead->wallet_id ?? '', $lead->mt5_id ?? '');

            if ($result['found'] && $hfm->meetsMinDeposit($result['deposit'])) {
                $lead->update(['hfm_deposit' => $result['deposit'], 'hfm_equity' => $result['equity']]);
                $handler->verifyHfmAccount($lead, $lead->telegram_id);
            }
        }
    }
}
