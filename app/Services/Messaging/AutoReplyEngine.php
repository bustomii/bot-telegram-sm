<?php

namespace App\Services\Messaging;

use App\Models\AutoReplyRule;
use App\Models\MessagingAccount;
use App\Models\MessageLog;
use Illuminate\Support\Facades\Log;

class AutoReplyEngine
{
    public function __construct(
        private readonly MessagingManager $messagingManager,
    ) {}

    /**
     * @param  array<string, mixed>  $update
     */
    public function processIncoming(MessagingAccount $account, array $update, string $chatId, string $text): void
    {
        $account->update(['last_activity_at' => now()]);

        MessageLog::query()->create([
            'messaging_account_id' => $account->id,
            'direction' => MessageLog::DIRECTION_IN,
            'external_chat_id' => $chatId,
            'content' => $text,
            'payload' => $update,
        ]);

        $rule = $this->matchRule($account, $text);

        if (! $rule) {
            return;
        }

        $sent = $this->messagingManager
            ->forAccount($account)
            ->sendText($account, $chatId, $rule->response_message);

        if (! $sent) {
            Log::warning('Auto reply failed to send', [
                'account_id' => $account->id,
                'rule_id' => $rule->id,
            ]);

            return;
        }

        MessageLog::query()->create([
            'messaging_account_id' => $account->id,
            'auto_reply_rule_id' => $rule->id,
            'direction' => MessageLog::DIRECTION_OUT,
            'external_chat_id' => $chatId,
            'content' => $rule->response_message,
        ]);
    }

    public function matchRule(MessagingAccount $account, string $text): ?AutoReplyRule
    {
        $rules = $account->autoReplyRules()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        $defaultRule = null;

        foreach ($rules as $rule) {
            if ($rule->trigger_type === AutoReplyRule::TRIGGER_DEFAULT) {
                $defaultRule ??= $rule;

                continue;
            }

            if ($this->matches($rule, $text)) {
                return $rule;
            }
        }

        return $defaultRule;
    }

    private function matches(AutoReplyRule $rule, string $text): bool
    {
        $pattern = $rule->trigger_pattern ?? '';
        $haystack = $rule->match_case_sensitive ? $text : mb_strtolower($text);
        $needle = $rule->match_case_sensitive ? $pattern : mb_strtolower($pattern);

        return match ($rule->trigger_type) {
            AutoReplyRule::TRIGGER_EXACT => $haystack === $needle,
            AutoReplyRule::TRIGGER_CONTAINS => $needle !== '' && str_contains($haystack, $needle),
            AutoReplyRule::TRIGGER_STARTS_WITH => $needle !== '' && str_starts_with($haystack, $needle),
            AutoReplyRule::TRIGGER_REGEX => $pattern !== '' && @preg_match($pattern, $text) === 1,
            default => false,
        };
    }
}
