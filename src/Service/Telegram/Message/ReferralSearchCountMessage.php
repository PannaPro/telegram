<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ReferralSearchCountMessage
{
    public function __construct(
        private TelegramBotService $bot,
    )
    {
    }

    public function sendMessage(int $chatId, string $dateMessage): int
    {
        $text = <<<MARKDOWN
        *Поиск по участникам (статус participant) $dateMessage*

        Введите минимальное кол-во рефералов которое должен иметь пользователь:
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Вернутся к выбору периода', 'callback_data' => 'back_to_chose_search_date'],
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
