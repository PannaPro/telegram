<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotMessaging\BotMessengerInterface;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class AdminGameMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
    ) {
    }

    public function sendMessage(int $chatId, array $events): int
    {
        $lines = [""];

        $hasEvents = !empty($events['current']) || !empty($events['upcoming']);

        if (!$hasEvents) {
            $text = "❗ Нет событий";
        } else {
            $lines[] = "🚀 Текущие события:";
            if (!empty($events['current'])) {
                foreach ($events['current'] as $e) {
                    $lines[] = "{$e['name']}: с {$e['from']} по {$e['to']}";
                }
            } else {
                $lines[] = "Отсутствуют";
            }
            $lines[] = "";

            $lines[] = "⏳ Предстоящие события (всего: {$events['totalUpcoming']}):";
            if (!empty($events['upcoming'])) {
                foreach ($events['upcoming'] as $e) {
                    $lines[] = "{$e['name']}: с {$e['from']} по {$e['to']}";
                }
            } else {
                $lines[] = "Отсутствуют";
            }
            $lines[] = "";

            $text = implode("\n", $lines);
        }

        if ($hasEvents) {
            $keyboard = [
                ['✏️ Управлять событием'],
                ['🎮 Создать событие'],
                ['⬅ Вернуться в меню'],
            ];
        } else {
            $keyboard = [
                ['🎮 Создать событие'],
                ['⬅ Вернуться в меню'],
            ];
        }

        $replyKeyboard = new ReplyKeyboardMarkup(
            $keyboard,
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
            $replyKeyboard
        );

        return $message->getMessageId();
    }
}
