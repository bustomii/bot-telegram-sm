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
        $this->persistSettings($request);

        return back()->with('success', 'Konfigurasi bot berhasil disimpan.');
    }

    public function setWebhook(Request $request): RedirectResponse
    {
        $this->persistSettings($request);

        $settings = BotSetting::current()->fresh();

        if (empty($settings->telegram_bot_token)) {
            return back()->with('error', 'Bot Token wajib diisi sebelum set webhook.');
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

    private function persistSettings(Request $request): void
    {
        $this->normalizeConfigInput($request);

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
            'is_active' => 'nullable|boolean',
            'pdf_registration' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_ib_step1' => 'nullable|file|mimes:pdf|max:10240',
            'pdf_ib_step2' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if (! empty($validated['telegram_bot_token'])) {
            $validated['telegram_bot_token'] = trim($validated['telegram_bot_token']);
        }

        foreach (['pdf_registration', 'pdf_ib_step1', 'pdf_ib_step2'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('pdfs', 'public');
                $validated[$field] = $path;
            } else {
                unset($validated[$field]);
            }
        }

        $validated['is_active'] = $request->boolean('is_active');

        BotSetting::current()->update($validated);
    }

    private function normalizeConfigInput(Request $request): void
    {
        foreach ([
            'community_link',
            'hfm_referral_link',
            'hfm_api_url',
            'telegram_webhook_secret',
            'admin_group_chat_id',
            'hfm_api_key',
            'hfm_ib_id',
        ] as $field) {
            if ($request->has($field) && $request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        if ($request->has('telegram_bot_token') && is_string($request->input('telegram_bot_token'))) {
            $request->merge([
                'telegram_bot_token' => trim($request->input('telegram_bot_token')) ?: null,
            ]);
        }
    }
}
