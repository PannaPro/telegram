<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\UserMenuMessage;
use App\Service\Telegram\TelegramMessageCache;

class MenuService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private UserMenuMessage $message,
    ) {
    }

    public function sendStartMenu(int $chatId): void
    {
        $messageId = $this->message->sendMenu($chatId);

        $this->cache->saveAndClean(TelegramCacheKey::START_MENU, $chatId, $messageId);
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }

    public function sendPreview(int $chatId, int $currentMessage): void
    {
        $messageId = $this->message->sendPreview($chatId);

        $this->cache->saveAndClean(TelegramCacheKey::START_MENU, $chatId, $messageId);
        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
