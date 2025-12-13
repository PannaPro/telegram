<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use CURLFile;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ParticipateMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId, string $username, CURLFile $photo): int
    {
        $caption = <<<MARKDOWN
        @$username, все наши игры проходят в боте [Gamee](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0)

        Я сгенерировал для тебя аватарку с твоим игровым номером — она прикреплена выше.

        Установи ee в игровом боте (в левом верхнем углу), чтобы я мог точно определить тебя в случае победы и выдать приз.

        👉 [Открыть игру и установить аватарку](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0)

        ⚠️Без установленной аватарки мы не сможем засчитать участие.
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "Готово!", 'callback_data' => 'avatarSet'],
            ],
        ]);

        $message = $this->bot->sendPhoto(
            $chatId,
            $photo,
            $caption,
            null,
            $keyboard,
            false,
            TelegramParseMode::MARKDOWN
        );

        return $message->getMessageId();
    }

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }
}
