<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Cache\TelegramMessageCache;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\UserMenuMessage;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;

class MenuService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private UserMenuMessage $message,
    ) {
    }

    public function sendStartMenu(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $messageId = $this->message->sendMenu($chatId);

        $currentMessage->delete($this->cache, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::START_MENU, $chatId, $messageId);
    }

    public function sendPreview(int $chatId): void
    {
        $messageId = $this->message->sendPreview($chatId);

        $this->cache->replaceMessage(TelegramCacheKey::START_MENU, $chatId, $messageId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }
}
