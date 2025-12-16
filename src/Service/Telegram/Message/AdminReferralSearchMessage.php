<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class AdminReferralSearchMessage
{
    public function __construct(
        private TelegramBotService $bot,
    )
    {
    }

    public function sendParticipantStatusMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
        *Поиск по рефералам:*

        Выберите статус рефералов.
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Участник', 'callback_data' => 'participant_referral'],
                ['text' => 'Участник +ЦД', 'callback_data' => 'participant_cd_referral'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_referral_menu'],
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

    public function editDataMessage(int $chatId, int $messageId, string $textHeader): void
    {
        $text = <<<MARKDOWN
        *Поиск по рефералам:*
        $textHeader

        Выбери дату для поиска.

        Для альтернативного поиска введите дату в формате:
        *01-12-2025*
        *01-12-2025 31-12-2025*
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'За все время', 'callback_data' => 'all_period'],
                ['text' => 'Сегодня', 'callback_data' => 'current_day_period'],
                ['text' => 'За неделю', 'callback_data' => 'week_period'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_referral_status'],
                ['text' => 'Главное меню', 'callback_data' => 'back_to_admin_menu'],
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

    public function editParticipantMessage(int $chatId, int $messageId): void
    {
        $text = <<<MARKDOWN
        *Поиск по рефералам:*

        Выберите статус рефералов.
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Участник', 'callback_data' => 'participant_referral'],
                ['text' => 'Участник +ЦД', 'callback_data' => 'participant_cd_referral'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_referral_menu'],
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

    public function editCountMessage(int $chatId, int $messageId, string $textHeader): void
    {
        $text = <<<MARKDOWN
        *Поиск по рефералам:*
        $textHeader

        Введите минимальное кол-во рефералов которое должен иметь пользователь:
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_search_date'],
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

    public function editReferralSearchMessage(int $chatId, int $messageId, string $textHeader): void
    {
        $text = <<<MARKDOWN
        *Поиск по рефералам:*
        $textHeader
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '🔍 Найти', 'callback_data' => 'search_referral'],
            ],
            [
                ['text' => '✏️ Редактировать', 'callback_data' => 'back_to_referral_status'],
            ],
            [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'back_to_admin_menu'],
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

    public function editSearchMessage(int $chatId, int $messageId): void
    {
        $text = <<<MARKDOWN
        🔍 выполняется поиск...
        MARKDOWN;

        $this->bot->editMessageText(
            $chatId,
            $messageId,
            $text,
            TelegramParseMode::MARKDOWN,
        );
    }
}
