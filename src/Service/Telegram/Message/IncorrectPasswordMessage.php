<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class IncorrectPasswordMessage
{
    public function __construct(
        private TelegramBotService $bot,

    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
            Не правильный пароль.
            Введите пароль еще раз:
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "Закрыть", 'callback_data' => 'close_password_menu'],
            ],
        ]);

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        return $message->getMessageId();
    }
}
