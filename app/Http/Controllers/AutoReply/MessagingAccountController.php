<?php

namespace App\Http\Controllers\AutoReply;

use App\Http\Controllers\Controller;
use App\Models\AutoReplyRule;
use App\Models\MessagingAccount;
use App\Models\MessagingProvider;
use App\Services\Messaging\MessagingManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MessagingAccountController extends Controller
{
    public function __construct(
        private readonly MessagingManager $messagingManager,
    ) {}

    public function index(Request $request): Response
    {
        $accounts = MessagingAccount::query()
            ->where('user_id', $request->user()->id)
            ->with('provider')
            ->withCount(['autoReplyRules', 'messageLogs'])
            ->latest()
            ->get()
            ->map(fn (MessagingAccount $account) => [
                ...$account->toArray(),
                'webhook_url' => $account->webhookUrl(),
            ]);

        $providers = MessagingProvider::query()->orderBy('sort_order')->get();

        return Inertia::render('AutoReply/Accounts/Index', [
            'accounts' => $accounts,
            'providers' => $providers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider_key' => ['required', 'string', 'exists:messaging_providers,key'],
            'account_type' => ['required', 'string', 'in:bot,user'],
            'label' => ['required', 'string', 'max:120'],
            'bot_token' => ['required_if:account_type,bot', 'nullable', 'string', 'max:255'],
        ]);

        $provider = MessagingProvider::query()
            ->where('key', $validated['provider_key'])
            ->firstOrFail();

        if (! $provider->is_enabled) {
            return back()->withErrors(['provider_key' => 'Provider belum tersedia.']);
        }

        if (! $provider->supportsAccountType($validated['account_type'])) {
            return back()->withErrors(['account_type' => 'Tipe akun tidak didukung provider ini.']);
        }

        $credentials = $validated['account_type'] === MessagingAccount::TYPE_BOT
            ? ['bot_token' => trim((string) $validated['bot_token'])]
            : [];

        try {
            $identity = $this->messagingManager
                ->driver($provider->key)
                ->validateCredentials($validated['account_type'], $credentials);
        } catch (\Throwable $exception) {
            return back()->withErrors(['bot_token' => $exception->getMessage()]);
        }

        $account = MessagingAccount::query()->create([
            'user_id' => $request->user()->id,
            'messaging_provider_id' => $provider->id,
            'account_type' => $validated['account_type'],
            'label' => $validated['label'],
            'external_id' => $identity['external_id'],
            'username' => $identity['username'] ?? null,
            'display_name' => $identity['display_name'],
            'credentials' => $credentials,
            'status' => MessagingAccount::STATUS_DISCONNECTED,
            'webhook_secret' => Str::random(48),
            'metadata' => $identity['metadata'] ?? [],
        ]);

        try {
            $this->messagingManager->forAccount($account)->connect($account);
        } catch (\Throwable $exception) {
            $account->update([
                'status' => MessagingAccount::STATUS_ERROR,
                'status_message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['bot_token' => $exception->getMessage()]);
        }

        return redirect()
            ->route('auto-reply.accounts.show', $account)
            ->with('success', 'Akun berhasil dihubungkan.');
    }

    public function show(Request $request, MessagingAccount $account): Response
    {
        $this->authorizeAccount($request, $account);

        $account->load(['provider', 'autoReplyRules' => fn ($q) => $q->orderByDesc('priority')]);

        $recentLogs = $account->messageLogs()
            ->with('rule:id,name')
            ->latest()
            ->limit(20)
            ->get();

        return Inertia::render('AutoReply/Accounts/Show', [
            'account' => [
                ...$account->toArray(),
                'webhook_url' => $account->webhookUrl(),
            ],
            'triggerTypes' => AutoReplyRule::triggerTypes(),
            'recentLogs' => $recentLogs,
        ]);
    }

    public function reconnect(Request $request, MessagingAccount $account): RedirectResponse
    {
        $this->authorizeAccount($request, $account);

        try {
            $this->messagingManager->forAccount($account)->connect($account->fresh());
        } catch (\Throwable $exception) {
            $account->update([
                'status' => MessagingAccount::STATUS_ERROR,
                'status_message' => $exception->getMessage(),
            ]);

            return back()->withErrors(['account' => $exception->getMessage()]);
        }

        return back()->with('success', 'Webhook berhasil diaktifkan kembali.');
    }

    public function destroy(Request $request, MessagingAccount $account): RedirectResponse
    {
        $this->authorizeAccount($request, $account);

        try {
            $this->messagingManager->forAccount($account)->disconnect($account);
        } catch (\Throwable) {
            // ignore disconnect errors
        }

        $account->delete();

        return redirect()
            ->route('auto-reply.accounts.index')
            ->with('success', 'Akun dihapus.');
    }

    private function authorizeAccount(Request $request, MessagingAccount $account): void
    {
        abort_unless($account->user_id === $request->user()->id, 403);
    }
}
