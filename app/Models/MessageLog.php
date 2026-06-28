<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    public const DIRECTION_IN = 'in';

    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'messaging_account_id',
        'auto_reply_rule_id',
        'direction',
        'external_chat_id',
        'external_message_id',
        'content',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(MessagingAccount::class, 'messaging_account_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutoReplyRule::class, 'auto_reply_rule_id');
    }
}
