<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotMessaging\BotMessengerInterface;
use CURLFile;

class GameMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
    ) {
    }

    public function sendMessage(int $chatId): int
    {
        $caption = <<<MARKDOWN
        👋 *Заходи в наш игровой* [канал](https://t.me/PAKETAGAME) *и учавствуй в играх!*
        MARKDOWN;

        $photoPath = '/app/public/image/game.jpg';

        $message = $this->bot->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            $caption,
            null,
            null,
            false,
            TelegramParseMode::MARKDOWN,
        );

        return $message->getMessageId();
    }
}
