<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\GameMessage;
use App\Service\Telegram\TelegramMessageCache;

class GameService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private GameMessage $message,
    ) {
    }

    public function makeAction(int $chatId, int $currentMessage): void
    {
        $messageId = $this->message->sendMessage($chatId);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
