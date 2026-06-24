<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'telegram_id',
        'telegram_username',
        'name',
        'join_purpose',
        'trading_experience',
        'has_hfm_account',
        'full_name',
        'wallet_id',
        'mt5_id',
        'hfm_account_name',
        'hfm_email',
        'hfm_phone',
        'hfm_ib_status',
        'hfm_equity',
        'hfm_deposit',
        'hfm_registered_at',
        'status',
        'previous_status',
        'conversation_step',
        'bot_paused',
        'assigned_admin_id',
        'admin_notes',
        'reject_reason',
        'last_activity_at',
        'follow_up_30m_at',
        'follow_up_24h_at',
    ];

    protected function casts(): array
    {
        return [
            'has_hfm_account' => 'boolean',
            'bot_paused' => 'boolean',
            'hfm_equity' => 'decimal:2',
            'hfm_deposit' => 'decimal:2',
            'hfm_registered_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'follow_up_30m_at' => 'datetime',
            'follow_up_24h_at' => 'datetime',
            'status' => LeadStatus::class,
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function supportCases(): HasMany
    {
        return $this->hasMany(SupportCase::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function logActivity(string $action, ?string $fromStatus = null, ?string $toStatus = null, ?string $message = null, ?array $metadata = null, ?int $userId = null): void
    {
        $this->activities()->create([
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'message' => $message,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);
    }

    public function transitionTo(LeadStatus $status, string $action, ?string $message = null): void
    {
        $from = $this->status?->value;
        $this->update([
            'status' => $status,
            'last_activity_at' => now(),
        ]);
        $this->logActivity($action, $from, $status->value, $message);
    }
}
