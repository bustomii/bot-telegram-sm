<?php

namespace App\Http\Controllers\AutoReply;

use App\Http\Controllers\Controller;
use App\Models\AutoReplyRule;
use App\Models\MessagingAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AutoReplyRuleController extends Controller
{
    public function store(Request $request, MessagingAccount $account): RedirectResponse
    {
        $this->authorizeAccount($request, $account);

        $validated = $this->validateRule($request);

        $account->autoReplyRules()->create($validated);

        return back()->with('success', 'Aturan auto reply ditambahkan.');
    }

    public function update(Request $request, MessagingAccount $account, AutoReplyRule $rule): RedirectResponse
    {
        $this->authorizeAccount($request, $account);
        abort_unless($rule->messaging_account_id === $account->id, 404);

        $validated = $this->validateRule($request);

        $rule->update($validated);

        return back()->with('success', 'Aturan diperbarui.');
    }

    public function destroy(Request $request, MessagingAccount $account, AutoReplyRule $rule): RedirectResponse
    {
        $this->authorizeAccount($request, $account);
        abort_unless($rule->messaging_account_id === $account->id, 404);

        $rule->delete();

        return back()->with('success', 'Aturan dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRule(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'trigger_type' => ['required', Rule::in(array_keys(AutoReplyRule::triggerTypes()))],
            'trigger_pattern' => [
                Rule::requiredIf(fn () => $request->input('trigger_type') !== AutoReplyRule::TRIGGER_DEFAULT),
                'nullable',
                'string',
                'max:500',
            ],
            'response_message' => ['required', 'string', 'max:4000'],
            'is_active' => ['sometimes', 'boolean'],
            'match_case_sensitive' => ['sometimes', 'boolean'],
            'priority' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['match_case_sensitive'] = $request->boolean('match_case_sensitive', false);
        $validated['priority'] = (int) ($validated['priority'] ?? 0);

        if ($validated['trigger_type'] === AutoReplyRule::TRIGGER_DEFAULT) {
            $validated['trigger_pattern'] = null;
        }

        return $validated;
    }

    private function authorizeAccount(Request $request, MessagingAccount $account): void
    {
        abort_unless($account->user_id === $request->user()->id, 403);
    }
}
