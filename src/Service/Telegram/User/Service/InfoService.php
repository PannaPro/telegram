<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\InfoMessage;
use App\Service\Telegram\TelegramMessageCache;

final class InfoService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private InfoMessage $message,
    ) {
    }

    public function makeAction(int $chatId, int $currentMessage): void
    {
        $messageId = $this->message->sendInfo($chatId);

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }
}
