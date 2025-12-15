<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ReferralSearchDateMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId, string $textHeader): int
    {
        $text = <<<MARKDOWN
        $textHeader

        Выбери дату для поиска.

        Для альтернативного поиска введите команду в формате:
        *День 01-12-2025*
        *Даты 01-12-2025 31-12-2025*
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'За все время', 'callback_data' => 'all_period'],
                ['text' => 'Сегодня', 'callback_data' => 'current_day_period'],
                ['text' => 'За неделю', 'callback_data' => 'week_period'],
            ],
            [
                ['text' => 'Назад', 'callback_data' => 'back_to_referral_menu'],
                ['text' => 'Главное меню', 'callback_data' => 'back_to_admin_menu'],
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
