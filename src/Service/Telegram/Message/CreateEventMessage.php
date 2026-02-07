<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Context\Dto\CreateEventContext;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotMessaging\BotMessengerInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class CreateEventMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
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
                ['text' => $eventType->getName(), 'callback_data' => 'create_event_type_' . $eventType->getId()]
            ];
        }

        $buttons[] = [
            ['text' => '⬅ Вернуться в меню', 'callback_data' => 'create_event_back_to_events_menu']
        ];

        $keyboard = new InlineKeyboardMarkup($buttons);

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
                ['text' => $eventType->getName(), 'callback_data' => 'create_event_type_' . $eventType->getId()]
            ];
        }

        $buttons[] = [
            ['text' => '⬅ Вернуться в меню', 'callback_data' => 'create_event_back_to_events_menu']
        ];

        $keyboard = new InlineKeyboardMarkup($buttons);

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

    public function editEventNameMessage(int $chatId, int $messageId, string $currentData = ''): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

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
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_group_selection'],
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
                ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_group_selection'],
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

        {$context->getFormattedText(true)}

        Подтвердить или отредактировать?
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✅ Подтвердить', 'callback_data' => 'create_event_confirm'],
                ['text' => '✏️ Редактировать', 'callback_data' => 'create_event_edit'],
            ],
            [
                ['text' => '⬅ Вернуться в меню', 'callback_data' => 'create_event_back_to_events_menu'],
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

        {$context->getFormattedText(true)}

        Подтвердить или отредактировать?
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✅ Подтвердить', 'callback_data' => 'create_event_confirm'],
                ['text' => '✏️ Редактировать', 'callback_data' => 'create_event_edit'],
            ],
            [
                ['text' => '⬅ Вернуться в меню', 'callback_data' => 'create_event_back_to_events_menu'],
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

    public function sendGroupSelectionMessage(int $chatId, string $currentData, array $groups, int $page = 1): int
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Выберите группу для проведения события:
        MARKDOWN;

        $keyboard = $this->buildGroupSelectionKeyboard($groups, $page);

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

    public function editGroupSelectionMessage(int $chatId, int $messageId, string $currentData, array $groups, int $page = 1): void
    {
        $text = <<<MARKDOWN
        *Создание события*

        $currentData

        Выберите группу для проведения события:
        MARKDOWN;

        $keyboard = $this->buildGroupSelectionKeyboard($groups, $page);

        $this->bot->editMessageText(
            $chatId,
            $messageId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            $keyboard
        );
    }

    private function buildGroupSelectionKeyboard(array $groups, int $page): InlineKeyboardMarkup
    {
        $buttons = [];
        $perPage = 5;
        $totalGroups = count($groups);
        $totalPages = (int)ceil($totalGroups / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $groupsOnPage = array_slice($groups, $offset, $perPage);

        // Add group buttons
        foreach ($groupsOnPage as $group) {
            $buttons[] = [
                ['text' => $group->getTitle(), 'callback_data' => 'create_event_group_' . $group->getId()]
            ];
        }

        // Add pagination row if needed
        if ($totalPages > 1) {
            $paginationRow = [];

            if ($page > 1) {
                $paginationRow[] = ['text' => '⬅', 'callback_data' => 'create_event_group_page_' . ($page - 1)];
            }

            $paginationRow[] = ['text' => "{$page}/{$totalPages}", 'callback_data' => 'create_event_group_page_current'];

            if ($page < $totalPages) {
                $paginationRow[] = ['text' => '➡', 'callback_data' => 'create_event_group_page_' . ($page + 1)];
            }

            $buttons[] = $paginationRow;
        }

        // Add back button
        $buttons[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'create_event_back_to_end_date'],
        ];

        return new InlineKeyboardMarkup($buttons);
    }
}
