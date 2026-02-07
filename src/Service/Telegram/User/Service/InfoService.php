<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\InfoMessage;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;
use App\Service\Telegram\TelegramMessageCache;

final class InfoService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private InfoMessage $message,
    ) {
    }

    public function makeAction(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $messageId = $this->message->sendInfo($chatId);

        $currentMessage->delete($this->cache, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
