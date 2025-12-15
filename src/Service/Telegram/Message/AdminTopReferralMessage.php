<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class AdminTopReferralMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }

    public function sendMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
            Поиск по рефералам:
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Рефералы', 'callback_data' => 'participant_referral'],
                ['text' => 'Рефералы +ЦД', 'callback_data' => 'target_action_referral'],
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

    public function sendErrorMessage(int $chatId, string $text): int
    {
        $message = $this->bot->sendMessage($chatId, $text, TelegramParseMode::MARKDOWN);

        return $message->getMessageId();
    }

    public function sendApproveMessage(int $chatId, string $text): int
    {
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '🔍 Найти', 'callback_data' => 'search_referral'],
            ],
            [
                ['text' => '✏️ Редактировать', 'callback_data' => 'back_to_chose_search_date'],
            ],
            [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'back_to_admin_menu'],
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
