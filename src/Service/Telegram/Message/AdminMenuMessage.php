<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotMessaging\BotMessengerInterface;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class AdminMenuMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
            👋 Режим администратора
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['🎮 События'],
                ['👥 Рефералы'],
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
