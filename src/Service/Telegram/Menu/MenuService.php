<?php

namespace App\Service\Telegram\Menu;

use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use CURLFile;

class MenuService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private TelegramBotService $telegramBotService,
    ) {
    }

    public function sendStartMenu(int $chatId, int $messageId): void
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

        $text = <<<MARKDOWN
            👋 *Добро пожаловать в PAKETAGAME!*

            Присоединяйся к борьбе за крутые призы!
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['🎲 Игры'],
                ['👕 Получить номер'],
                ['💡 Инфо'],
            ],
            false,
            true,
            true
        );

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'markdown',
            false,
            null,
            $replyKeyboard,
        );

        $this->cache->clear('startMenu', $chatId, $messageId, $message->getMessageId());
        $this->cache->cleanup('step', $chatId);
    }

    public function sendPreview(int $chatId, int $messageId): void
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

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $keyboard
        );

        $this->cache->clear('startMenu', $chatId, $messageId, $message->getMessageId());
        $this->cache->cleanup('step', $chatId);
    }

    public function needChanelSubscribe(int $chatId, int $messageId): void
    {
        $text = <<<MARKDOWN
            👋 *Привет, дорогой друг!*

            Чтобы пользоватсья игровым ботом нужна подписка на наш [канал](https://t.me/PAKETAGAME?start=1)
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Проверить подписку', 'callback_data' => 'subscription']
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $keyboard
        );

        $this->cache->clear('startMenu', $chatId, $messageId, $message->getMessageId());
        $this->cache->cleanup('step', $chatId);
    }
}
