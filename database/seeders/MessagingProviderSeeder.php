<?php

namespace Database\Seeders;

use App\Models\MessagingProvider;
use Illuminate\Database\Seeder;

class MessagingProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'key' => 'telegram',
                'name' => 'Telegram',
                'icon' => 'send',
                'color' => '#229ED9',
                'is_enabled' => true,
                'supported_account_types' => ['bot', 'user'],
                'sort_order' => 1,
            ],
            [
                'key' => 'discord',
                'name' => 'Discord',
                'icon' => 'message-circle',
                'color' => '#5865F2',
                'is_enabled' => false,
                'supported_account_types' => ['bot'],
                'sort_order' => 2,
            ],
            [
                'key' => 'whatsapp',
                'name' => 'WhatsApp',
                'icon' => 'phone',
                'color' => '#25D366',
                'is_enabled' => false,
                'supported_account_types' => ['bot', 'user'],
                'sort_order' => 3,
            ],
        ];

        foreach ($providers as $provider) {
            MessagingProvider::query()->updateOrCreate(
                ['key' => $provider['key']],
                $provider,
            );
        }
    }
}
