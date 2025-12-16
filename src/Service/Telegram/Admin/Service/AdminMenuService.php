<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminMenuMessage;
use App\Service\Telegram\TelegramMessageCache;

class AdminMenuService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private AdminMenuMessage $message,
    ) {
    }

    public function handle(int $chatId, int $currentMessage = 0): void
    {
        $messageId = $this->message->sendMessage($chatId);

        $this->cache->deleteMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $currentMessage, $messageId);
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }
}
