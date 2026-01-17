<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Context\Dto\CreateEventContext;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class CreateEventMessage
{
    public function __construct(
        private TelegramBotService $bot,
    )
    {
    }

    public function sendEventTypeMessage(int $chatId, array $eventTypes): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        Выберите категорию события:
        MARKDOWN;

        $buttons = [];
        foreach ($eventTypes as $eventType) {
            $buttons[] = [
                'text' => $eventType->getName(),
                'callback_data' => 'create_event_type_' . $eventType->getId(),
            ];
        }

        $keyboard = new InlineKeyboardMarkup([
            $buttons,
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_admin_menu'],
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

    public function editEventTypeMessage(int $chatId, int $messageId, array $eventTypes): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        Выберите категорию события:
        MARKDOWN;

        $buttons = [];
        foreach ($eventTypes as $eventType) {
            $buttons[] = [
                'text' => $eventType->getName(),
                'callback_data' => 'create_event_type_' . $eventType->getId(),
            ];
        }

        $keyboard = new InlineKeyboardMarkup([
            $buttons,
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'back_to_admin_menu'],
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

    public function sendEventNameMessage(int $chatId): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        Введите название события:
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_type'],
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

    public function editEventNameMessage(int $chatId, int $messageId): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        Введите название события:
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_type'],
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

    public function sendEventDatesMessage(int $chatId, string $currentData): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите дату и время начала события в формате: дд-мм-гггг чч:мм
        Например: 01-02-2026 10:00
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_name'],
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

    public function editEventDatesMessage(int $chatId, int $messageId, string $currentData): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите дату и время начала события в формате: дд-мм-гггг чч:мм
        Например: 01-02-2026 10:00
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_name'],
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

    public function sendEventEndDateMessage(int $chatId, string $currentData): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите дату и время окончания события в формате: дд-мм-гггг чч:мм
        Например: 21-02-2026 18:00
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_start_date'],
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

    public function editEventEndDateMessage(int $chatId, int $messageId, string $currentData): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите дату и время окончания события в формате: дд-мм-гггг чч:мм
        Например: 21-02-2026 18:00
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_start_date'],
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

    public function sendPartnerLinkMessage(int $chatId, string $currentData): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите ссылку на партнерский канал (опционально):
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Пропустить', 'callback_data' => 'create_event_skip_partner_link'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_end_date'],
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

    public function editPartnerLinkMessage(int $chatId, int $messageId, string $currentData): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Введите ссылку на партнерский канал (опционально):
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Пропустить', 'callback_data' => 'create_event_skip_partner_link'],
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_end_date'],
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

    public function sendConfirmationMessage(int $chatId, CreateEventContext $context): int
    {
        $text = <<<MARKDOWN
        *Подтверждение создания события*

        {$context->getFormattedText()}

        Подтвердить или отредактировать?
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✅ Подтвердить', 'callback_data' => 'create_event_confirm'],
                ['text' => '✏️ Редактировать', 'callback_data' => 'create_event_edit'],
            ],
            [
                ['text' => '❌ Отменить', 'callback_data' => 'create_event_cancel'],
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

    public function editConfirmationMessage(int $chatId, int $messageId, CreateEventContext $context): void
    {
        $text = <<<MARKDOWN
        *Подтверждение создания события*

        {$context->getFormattedText()}

        Подтвердить или отредактировать?
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✅ Подтвердить', 'callback_data' => 'create_event_confirm'],
                ['text' => '✏️ Редактировать', 'callback_data' => 'create_event_edit'],
            ],
            [
                ['text' => '❌ Отменить', 'callback_data' => 'create_event_cancel'],
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

    public function sendErrorMessage(int $chatId, string $text): int
    {
        $message = $this->bot->sendMessage($chatId, $text, TelegramParseMode::MARKDOWN);

        return $message->getMessageId();
    }
}
