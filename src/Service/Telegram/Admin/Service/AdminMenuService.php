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

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::START_MENU, $chatId, $messageId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }
}
