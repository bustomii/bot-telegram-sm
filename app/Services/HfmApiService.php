<?php

namespace App\Services;

use App\Models\BotSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HfmApiService
{
    public function __construct(private BotSetting $settings) {}

    public static function make(): self
    {
        return new self(BotSetting::current());
    }

    /**
     * @return array{found: bool, account_name?: string, wallet_id?: string, mt5_id?: string, ib_status?: string, equity?: float, deposit?: float, registered_at?: string, email?: string, phone?: string}
     */
    public function verifyAccount(string $walletId, string $mt5Id): array
    {
        if (! $this->settings->hfm_api_url || ! $this->settings->hfm_api_key) {
            Log::warning('HFM API not configured, using mock response');

            return $this->mockVerify($walletId, $mt5Id);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->settings->hfm_api_key,
                'Accept' => 'application/json',
            ])->get($this->settings->hfm_api_url.'/accounts/verify', [
                'wallet_id' => $walletId,
                'mt5_id' => $mt5Id,
            ]);

            if (! $response->successful()) {
                return ['found' => false];
            }

            $data = $response->json('data', []);

            return [
                'found' => true,
                'account_name' => $data['account_name'] ?? null,
                'wallet_id' => $data['wallet_id'] ?? $walletId,
                'mt5_id' => $data['mt5_id'] ?? $mt5Id,
                'ib_status' => $data['ib_status'] ?? null,
                'equity' => (float) ($data['equity'] ?? 0),
                'deposit' => (float) ($data['deposit'] ?? 0),
                'registered_at' => $data['registered_at'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('HFM API error: '.$e->getMessage());

            return ['found' => false];
        }
    }

    public function isIbMatch(?string $ibStatus): bool
    {
        if (! $ibStatus) {
            return false;
        }

        return $ibStatus === $this->settings->hfm_ib_id || $ibStatus === 'matched';
    }

    public function meetsMinDeposit(float $deposit): bool
    {
        return $deposit >= (float) $this->settings->min_deposit;
    }

    private function mockVerify(string $walletId, string $mt5Id): array
    {
        if ($walletId === '000000' || $mt5Id === '000000') {
            return ['found' => false];
        }

        return [
            'found' => true,
            'account_name' => 'Demo Account',
            'wallet_id' => $walletId,
            'mt5_id' => $mt5Id,
            'ib_status' => $this->settings->hfm_ib_id ?? 'matched',
            'equity' => 50.00,
            'deposit' => 50.00,
            'registered_at' => now()->subDays(7)->toIso8601String(),
            'email' => 'demo@example.com',
            'phone' => null,
        ];
    }
}
