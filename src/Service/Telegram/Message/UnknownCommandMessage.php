<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class UnknownCommandMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
        😅 Ой! Кажется такой команды нет.
        MARKDOWN;

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
        );

        return $message->getMessageId();
    }
}
