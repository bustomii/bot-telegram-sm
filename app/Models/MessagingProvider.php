<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessagingProvider extends Model
{
    protected $fillable = [
        'key',
        'name',
        'icon',
        'color',
        'is_enabled',
        'supported_account_types',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'supported_account_types' => 'array',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(MessagingAccount::class);
    }

    public function supportsAccountType(string $type): bool
    {
        $types = $this->supported_account_types ?? [];

        return in_array($type, $types, true);
    }
}
