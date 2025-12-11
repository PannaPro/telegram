<?php

namespace App\Service\Telegram\Admin\AdminAction;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class AdminMenuService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private TelegramBotService $telegramBotService,
    ) {
    }

    public function handle(int $chatId, int $currentMessage = 0): void
    {
        $text = <<<MARKDOWN
            👋 Режим администратора
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['👥 Рефералы'],
                ['Выйти из режима администратора']
            ],
            false,
            true,
            true
        );

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $replyKeyboard,
        );

        $this->cache->clear(TelegramCacheKey::START_MENU, $chatId, $currentMessage, $message->getMessageId());
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }
}
