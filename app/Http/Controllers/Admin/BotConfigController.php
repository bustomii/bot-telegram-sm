<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotSetting;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BotConfigController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/BotConfig', [
            'settings' => BotSetting::current(),
            'webhookUrl' => route('telegram.webhook'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_bot_token' => 'nullable|string',
            'telegram_webhook_secret' => 'nullable|string',
            'admin_group_chat_id' => 'nullable|string',
            'community_link' => 'nullable|url',
            'hfm_referral_link' => 'nullable|url',
            'hfm_api_url' => 'nullable|url',
            'hfm_api_key' => 'nullable|string',
            'hfm_ib_id' => 'nullable|string',
            'min_deposit' => 'required|numeric|min:0',
            'welcome_message' => 'nullable|string',
            'is_active' => 'boolean',
            'pdf_registration' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_ib_step1' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_ib_step2' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if (! empty($validated['telegram_bot_token'])) {
            $validated['telegram_bot_token'] = trim($validated['telegram_bot_token']);
        }

        $settings = BotSetting::current();

        foreach (['pdf_registration', 'pdf_ib_step1', 'pdf_ib_step2'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('pdfs', 'public');
                $validated[$field] = $path;
            } else {
                unset($validated[$field]);
            }
        }

        $settings->update($validated);

        return back()->with('success', 'Konfigurasi bot berhasil disimpan.');
    }

    public function setWebhook(): RedirectResponse
    {
        $settings = BotSetting::current();

        if (empty($settings->telegram_bot_token)) {
            return back()->with('error', 'Bot Token belum disimpan. Klik "Simpan Konfigurasi" terlebih dahulu.');
        }

        $url = route('telegram.webhook');
        $result = TelegramService::make()->setWebhook($url);

        if (($result['ok'] ?? false) === true) {
            return back()->with('success', "Webhook Telegram berhasil diatur ke: {$url}");
        }

        $errorCode = $result['error_code'] ?? null;
        $description = $result['description'] ?? 'Unknown error';

        $message = match ($errorCode) {
            404 => 'Bot Token tidak valid. Salin ulang token dari @BotFather, simpan konfigurasi, lalu coba lagi.',
            401 => 'Bot Token ditolak oleh Telegram. Pastikan token benar dan bot belum dihapus.',
            default => "Gagal mengatur webhook: {$description}",
        };

        return back()->with('error', $message);
    }
}
