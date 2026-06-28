<?php

namespace App\Http\Controllers\AutoReply;

use App\Http\Controllers\Controller;
use App\Models\AutoReplyRule;
use App\Models\MessagingAccount;
use App\Models\MessagingProvider;
use App\Models\MessageLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $accounts = MessagingAccount::query()
            ->where('user_id', $userId)
            ->with('provider')
            ->withCount('autoReplyRules')
            ->latest()
            ->get();

        $stats = [
            'total_accounts' => $accounts->count(),
            'connected_accounts' => $accounts->where('status', MessagingAccount::STATUS_CONNECTED)->count(),
            'active_rules' => AutoReplyRule::query()
                ->whereHas('account', fn ($q) => $q->where('user_id', $userId))
                ->where('is_active', true)
                ->count(),
            'messages_today' => MessageLog::query()
                ->whereHas('account', fn ($q) => $q->where('user_id', $userId))
                ->whereDate('created_at', today())
                ->count(),
        ];

        $providers = MessagingProvider::query()
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('AutoReply/Dashboard', [
            'stats' => $stats,
            'accounts' => $accounts,
            'providers' => $providers,
        ]);
    }
}
