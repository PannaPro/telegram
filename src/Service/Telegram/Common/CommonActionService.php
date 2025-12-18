<?php

namespace App\Service\Telegram\Common;

use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;

readonly class CommonActionService
{
    use AnswerCallbackQueryTrait;

    public function __construct(
        private TelegramBotService $bot,
        private TelegramMessageCache $cache,
    ) {
    }

    public function deletePinnedMessage(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);

        $this->cache->deleteMessage('close_pinned_message', $chatId);
    }
}
