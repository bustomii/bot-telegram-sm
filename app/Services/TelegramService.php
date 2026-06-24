<?php

namespace App\Services;

use App\Models\BotSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function __construct(private BotSetting $settings) {}

    public static function make(): self
    {
        return new self(BotSetting::current());
    }

    private function apiUrl(string $method): string
    {
        return "https://api.telegram.org/bot{$this->settings->telegram_bot_token}/{$method}";
    }

    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): ?array
    {
        if (! $this->settings->telegram_bot_token) {
            Log::warning('Telegram bot token not configured');

            return null;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($replyMarkup) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $response = Http::post($this->apiUrl('sendMessage'), $payload);

        return $response->json();
    }

    public function sendDocument(int|string $chatId, string $documentPath, ?string $caption = null, ?array $replyMarkup = null): ?array
    {
        if (! $this->settings->telegram_bot_token) {
            return null;
        }

        $request = Http::attach('document', file_get_contents($documentPath), basename($documentPath))
            ->post($this->apiUrl('sendDocument'), array_filter([
                'chat_id' => $chatId,
                'caption' => $caption,
                'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
            ]));

        return $request->json();
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): void
    {
        Http::post($this->apiUrl('answerCallbackQuery'), array_filter([
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]));
    }

    public function setWebhook(string $url): ?array
    {
        return Http::post($this->apiUrl('setWebhook'), [
            'url' => $url,
            'secret_token' => $this->settings->telegram_webhook_secret,
        ])->json();
    }

    public static function inlineKeyboard(array $buttons): array
    {
        return ['inline_keyboard' => $buttons];
    }

    public static function button(string $text, string $callbackData): array
    {
        return ['text' => $text, 'callback_data' => $callbackData];
    }

    public static function urlButton(string $text, string $url): array
    {
        return ['text' => $text, 'url' => $url];
    }
}
