<?php

use App\Http\Controllers\Admin\BotConfigController;
use App\Http\Controllers\Admin\DashboardController as LegacyDashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Auth\TelegramAuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/auth/telegram/callback', [TelegramAuthController::class, 'callback'])
    ->middleware('guest')
    ->name('auth.telegram.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('admin.leads.index'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/overview', [LegacyDashboardController::class, 'index'])->name('overview');
        Route::get('/bot-config', [BotConfigController::class, 'edit'])->name('bot-config.edit');
        Route::match(['put', 'post'], '/bot-config', [BotConfigController::class, 'update'])->name('bot-config.update');
        Route::post('/bot-config/webhook', [BotConfigController::class, 'setWebhook'])->name('bot-config.webhook');

        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::post('/leads/{lead}/approve', [LeadController::class, 'approve'])->name('leads.approve');
        Route::post('/leads/{lead}/reject', [LeadController::class, 'reject'])->name('leads.reject');
        Route::post('/leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.notes');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
