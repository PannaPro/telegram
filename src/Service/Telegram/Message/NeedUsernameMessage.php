<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class NeedUsernameMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
        😅 Ой! Похоже, у тебя не указан юзернейм.

        Чтобы его добавить:
        1️⃣ Перейди в настройки Telegram.
        2️⃣ Найди поле "Имя пользователя".
        3️⃣ Придумай уникальный юзернейм и сохрани изменения.

        После этого сможешь участвовать в играх и получать призы! 🎉
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Я установил юзернейм', 'callback_data' => 'participate']
            ]
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
