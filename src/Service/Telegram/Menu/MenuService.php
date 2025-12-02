<?php

namespace App\Service\Telegram\Menu;

use App\Service\TelegramBotService;
use Redis;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class MenuService
{

    public function __construct(
        private Redis $redis,
        private TelegramBotService $telegramBotService,
    ) {
    }

    public function sendMenu(int $chatId): void
    {
        // TODO need test
        $type = 'main';
        $key = "telegram_menu:$chatId";
        $messages = json_decode($this->redis->get($key) ?: '[]', true);

        // Удаляем старые сообщения по флагу
        foreach ($messages as $msg) {
            if ($msg['delete']) {
                $this->telegramBotService->deleteMessage($chatId, $msg['id']);
            }
        }

        $keyboard = new ReplyKeyboardMarkup(
            [
                ['🎲 Игры'],
                ['💵 Торговля'],
                ['💰 Баланс', '👥 Рефералы'],
                ['🎫 Промокоды', '🛒 Магазин'],
                ['🏆 MVP', '💡 Инфо'],
            ],
            true,
            true,
            true
        );

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            "Выберите действие:",
            'Markdown',
            false,
            null,
            $keyboard
        );

        $messages[] = [
            'id' => $message->getMessageId(),
            'type' => $type,
            'created_at' => time()
        ];

        $this->redis->set($key, json_encode($messages));
    }
}
