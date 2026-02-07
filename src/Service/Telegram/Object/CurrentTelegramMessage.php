<?php

namespace App\Service\Telegram\Object;

use App\Service\Telegram\Cache\TelegramMessageCache;

final readonly class CurrentTelegramMessage implements DeletableTelegramMessageInterface
{
    public function __construct(
        private int $messageId
    ) {
    }

    public function delete(TelegramMessageCache $cache, int $chatId): void
    {
        $cache->deleteCurrentMessage($chatId, $this->messageId);
    }
}
