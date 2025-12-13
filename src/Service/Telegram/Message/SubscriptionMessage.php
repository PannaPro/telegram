<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SubscriptionMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendNeedSubscription(int $chatId): int
    {
        $text = <<<MARKDOWN
            👋 *Привет, дорогой друг!*

            Чтобы пользоваться игровым ботом нужна подписка на наш [канал](https://t.me/PAKETAGAME?start=1)
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Проверить подписку', 'callback_data' => 'subscription']
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

    public function checkSubscription(int $chatId): bool
    {
        return $this->bot->isSubscribed($chatId);
    }

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }

    public function editNeedSubscription(int $chatId, int $messageId): void
    {
        $text = <<<MARKDOWN
        ❌ *Подписка не найдена*

        Подпишись на [канал](https://t.me/PAKETAGAME?start=1) и нажми «Проверить подписку»
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Проверить подписку', 'callback_data' => 'subscription']
            ]
        ]);

        $this->bot->editMessageText(
            $chatId,
            $messageId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            $keyboard
        );
    }

}
