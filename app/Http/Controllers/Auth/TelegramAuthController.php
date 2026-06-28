<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\TelegramLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    public function __construct(
        private readonly TelegramLoginService $telegramLogin,
    ) {}

    public function callback(Request $request): RedirectResponse
    {
        $payload = $request->all();

        try {
            $this->telegramLogin->verify($payload);
        } catch (\InvalidArgumentException $exception) {
            return redirect()->route('login')->withErrors([
                'telegram' => $exception->getMessage(),
            ]);
        }

        $telegramUser = $this->telegramLogin->extractUser($payload);
        $fullName = trim($telegramUser['first_name'].' '.($telegramUser['last_name'] ?? ''));

        $user = User::query()->where('telegram_id', $telegramUser['id'])->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $fullName,
                'email' => 'telegram_'.$telegramUser['id'].'@bot.local',
                'password' => Str::password(32),
                'telegram_id' => $telegramUser['id'],
                'telegram_username' => $telegramUser['username'],
                'telegram_photo_url' => $telegramUser['photo_url'],
                'auth_provider' => 'telegram',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'name' => $fullName,
                'telegram_username' => $telegramUser['username'],
                'telegram_photo_url' => $telegramUser['photo_url'],
                'auth_provider' => 'telegram',
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route('auto-reply.dashboard'));
    }
}
