<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class AdminMenuMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
            👋 Режим администратора
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['👥 Рефералы'],
                ['1️⃣ Участники'],
                ['Выйти из режима администратора']
            ],
            false,
            true,
            true
        );

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $replyKeyboard,
        );

        return $message->getMessageId();
    }

}
