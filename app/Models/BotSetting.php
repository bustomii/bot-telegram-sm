<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    protected $fillable = [
        'telegram_bot_token',
        'telegram_webhook_secret',
        'admin_group_chat_id',
        'community_link',
        'hfm_referral_link',
        'hfm_api_url',
        'hfm_api_key',
        'hfm_ib_id',
        'min_deposit',
        'pdf_registration',
        'pdf_ib_step1',
        'pdf_ib_step2',
        'welcome_message',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_deposit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'welcome_message' => "Halo Kak 👋\n\nTerima kasih sudah menghubungi kami.\n\nSaya akan membantu proses bergabung ke komunitas.\n\nBoleh saya tahu nama Kakak?",
            'min_deposit' => 20,
            'is_active' => true,
        ]);
    }
}
