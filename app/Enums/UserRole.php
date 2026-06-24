<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case HeadAdmin = 'head_admin';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::HeadAdmin => 'Head Admin',
            self::Admin => 'Admin',
        };
    }

    public function canApprove(): bool
    {
        return in_array($this, [self::Owner, self::HeadAdmin]);
    }

    public function canManageAdmins(): bool
    {
        return $this === self::Owner;
    }

    public function canExport(): bool
    {
        return in_array($this, [self::Owner, self::HeadAdmin]);
    }
}
