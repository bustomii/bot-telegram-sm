<?php

namespace App\Services\Messaging;

use App\Contracts\Messaging\MessagingProviderContract;
use App\Models\MessagingAccount;
use App\Services\Messaging\Providers\DiscordProvider;
use App\Services\Messaging\Providers\TelegramProvider;
use App\Services\Messaging\Providers\WhatsAppProvider;
use InvalidArgumentException;

class MessagingManager
{
    /** @var array<string, class-string<MessagingProviderContract>> */
    private array $providers = [
        'telegram' => TelegramProvider::class,
        'discord' => DiscordProvider::class,
        'whatsapp' => WhatsAppProvider::class,
    ];

    public function driver(string $key): MessagingProviderContract
    {
        if (! isset($this->providers[$key])) {
            throw new InvalidArgumentException("Messaging provider [{$key}] is not registered.");
        }

        return app($this->providers[$key]);
    }

    public function forAccount(MessagingAccount $account): MessagingProviderContract
    {
        $account->loadMissing('provider');

        return $this->driver($account->provider->key);
    }
}
