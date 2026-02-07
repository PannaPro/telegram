<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotMessaging\BotMessengerInterface;
use CURLFile;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class UserMenuMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
    ) {
    }

    public function sendMenu(int $chatId): int
    {
//        $keyboard = new ReplyKeyboardMarkup(
//            [
//                ['🎲 Игры'],
//                ['💵 Торговля'],
//                ['💰 Баланс', '👥 Рефералы'],
//                ['🎫 Промокоды', '🛒 Магазин'],
//                ['🏆 MVP', '💡 Инфо'],
//            ],
//            true,
//            true,
//            true
//        );

        $caption = <<<MARKDOWN
            🚀 *Добро пожаловать в PAKETAGAME!*

            Присоединяйся к борьбе за крутые призы!
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['🎲 Игры'],
                ['👕 Получить номер'],
                ['👥 Рефералы'],
                ['💡 Инфо'],
            ],
            false,
            true,
            true
        );

        $photoPath = '/app/public/image/paketa.jpg';

        $message = $this->bot->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            $caption,
            null,
            $replyKeyboard,
            false,
            TelegramParseMode::MARKDOWN,
        );

        return $message->getMessageId();
    }

    public function sendPreview(int $chatId): int
    {
        $text = <<<MARKDOWN
            👋 *Рад приветствовать тебя в нашем клубе!*

            🖼 Для начала перейди в раздел "Стать участником" и получи свой игровой номер — он позволит мне идентифицировать тебя в случае победы. Этот шаг обязателен.

            После получения игрового номера ты сможешь участвовать в играх и претендовать на призы.

            📖 [Ссылка на телеграф с правилами](https://telegra.ph/...)
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '🎲 Стать участником', 'callback_data' => 'participate']
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

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }
}
