<?php

namespace App\Service\Telegram\Common;

use App\Service\Telegram\Cache\TelegramMessageCache;
use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\TelegramBotMessaging\BotMessengerInterface;

readonly class CommonActionService
{
    use AnswerCallbackQueryTrait;

    public function __construct(
        private BotMessengerInterface $bot,
        private TelegramMessageCache $cache,
    ) {
    }

    public function deletePinnedMessage(int $chatId, int $callbackId, int $messageId): void
    {
        $this->answerCallbackQuery($callbackId);

        $this->cache->selfDestructMessage($chatId, $messageId);
    }
}
