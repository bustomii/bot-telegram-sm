<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoReplyRule extends Model
{
    public const TRIGGER_EXACT = 'exact';

    public const TRIGGER_CONTAINS = 'contains';

    public const TRIGGER_STARTS_WITH = 'starts_with';

    public const TRIGGER_REGEX = 'regex';

    public const TRIGGER_DEFAULT = 'default';

    protected $fillable = [
        'messaging_account_id',
        'name',
        'trigger_type',
        'trigger_pattern',
        'response_message',
        'is_active',
        'match_case_sensitive',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'match_case_sensitive' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MessagingAccount::class, 'messaging_account_id');
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class);
    }

    public static function triggerTypes(): array
    {
        return [
            self::TRIGGER_EXACT => 'Exact match',
            self::TRIGGER_CONTAINS => 'Mengandung kata',
            self::TRIGGER_STARTS_WITH => 'Dimulai dengan',
            self::TRIGGER_REGEX => 'Regex',
            self::TRIGGER_DEFAULT => 'Default (fallback)',
        ];
    }
}
