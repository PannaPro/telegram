<?php

namespace App\Service\Telegram\Menu;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
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

    public function sendStartMenu(int $chatId, int $currentMessage = 0): void
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
            👋 *Добро пожаловать в PAKETAGAME!*

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

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            $caption,
            null,
            $replyKeyboard,
            false,
            TelegramParseMode::MARKDOWN,
        );

        $this->cache->clear(TelegramCacheKey::START_MENU, $chatId, $currentMessage, $message->getMessageId());
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }

    public function sendPreview(int $chatId): void
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
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $message->getMessageId());
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }
}
