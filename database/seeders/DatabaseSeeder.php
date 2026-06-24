<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\BotSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Owner,
            'email_verified_at' => now(),
        ]);

        BotSetting::current();
    }
}
