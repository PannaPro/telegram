<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Cache\TelegramMessageCache;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\GameMessage;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;

class GameService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private GameMessage $message,
    ) {
    }

    public function makeAction(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $messageId = $this->message->sendMessage($chatId);

        $currentMessage->delete($this->cache, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
