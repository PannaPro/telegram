<?php

namespace App\Service\Telegram\Menu;

use App\Service\TelegramBotService;
use Redis;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class MenuService
{

    public function __construct(
        private Redis $redis,
        private TelegramBotService $telegramBotService,
    ) {
    }

    public function sendStartMenu(int $chatId): void
    {
        $type = 'startMenu';
        $key = "/start:$chatId";

        $messagesJson = $this->redis->get($key);
        $messages = $messagesJson ? json_decode($messagesJson, true) : [];
        foreach ($messages as $msg) {
            if (isset($msg['delete']) && $msg['delete']) {
                try {
                    $this->telegramBotService->deleteMessage($chatId, $msg['id']);
                } catch (\Exception) {

                }
            }
        }
        unset($messages);

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
            👋 *Привет, дорогой друг!*
            MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['🎲 Участвовать'],
                ['💡 Инфо'],
            ],
            true,
            true,
            true
        );

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $replyKeyboard
        );

        $messages = [[
            'id' => $message->getMessageId(),
            'type' => $type,
            'delete' => true
        ]];

        $this->redis->set($key, json_encode($messages));
    }

    public function sendPreview(int $chatId): void
    {
        $type = 'startMenu';
        $key = "/start:$chatId";

        $messagesJson = $this->redis->get($key);
        $messages = $messagesJson ? json_decode($messagesJson, true) : [];
        foreach ($messages as $msg) {
            if (isset($msg['delete']) && $msg['delete']) {
                try {
                    $this->telegramBotService->deleteMessage($chatId, $msg['id']);
                } catch (\Exception) {

                }
            }
        }
        unset($messages);

        $text = <<<MARKDOWN
            👋 *Привет, дорогой друг!*

            Рад приветствовать тебя в нашем клубе!

            Сначала ознакомься с правилами.

            🖼 Для начала перейди в раздел "Стать участником" и получи свой порядковый номер — он позволит мне идентифицировать тебя в случае победы. Этот шаг обязателен.

            После получения идентификатора ты сможешь участвовать в играх и претендовать на призы.

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

        $messages = [[
            'id' => $message->getMessageId(),
            'type' => $type,
            'delete' => true
        ]];

        $this->redis->set($key, json_encode($messages));
    }
}
