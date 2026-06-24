<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'action',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public static function record(string $action, ?string $description = null, ?int $userId = null, ?int $leadId = null, ?array $metadata = null): void
    {
        static::create([
            'action' => $action,
            'description' => $description,
            'user_id' => $userId,
            'lead_id' => $leadId,
            'metadata' => $metadata,
        ]);
    }
}
