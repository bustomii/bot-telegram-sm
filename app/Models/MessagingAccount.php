<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessagingAccount extends Model
{
    use HasUuids;

    public const TYPE_BOT = 'bot';

    public const TYPE_USER = 'user';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'user_id',
        'messaging_provider_id',
        'account_type',
        'label',
        'external_id',
        'username',
        'display_name',
        'credentials',
        'status',
        'webhook_secret',
        'metadata',
        'status_message',
        'connected_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'metadata' => 'array',
            'connected_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(MessagingProvider::class, 'messaging_provider_id');
    }

    public function autoReplyRules(): HasMany
    {
        return $this->hasMany(AutoReplyRule::class)->orderByDesc('priority');
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public function isConnected(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    public function webhookUrl(): string
    {
        return url("/api/webhooks/{$this->provider->key}/{$this->id}");
    }
}
