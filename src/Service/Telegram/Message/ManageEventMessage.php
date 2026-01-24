<?php

namespace App\Service\Telegram\Message;

use App\Entity\Event;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ManageEventMessage
{
    public function __construct(
        private TelegramBotService $bot,
    )
    {
    }

    public function sendEventListMessage(int $chatId, array $events, int $page = 1): int
    {
        $text = <<<MARKDOWN
        *Управление событиями*

        Выберите событие для редактирования:
        MARKDOWN;

        $keyboard = $this->buildEventListKeyboard($events, $page);

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

    public function editEventListMessage(int $chatId, int $messageId, array $events, int $page = 1): void
    {
        $text = <<<MARKDOWN
        *Управление событиями*

        Выберите событие для редактирования:
        MARKDOWN;

        $keyboard = $this->buildEventListKeyboard($events, $page);

        $this->bot->editMessageText(
            $chatId,
            $messageId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            $keyboard
        );
    }

    private function buildEventListKeyboard(array $events, int $page): InlineKeyboardMarkup
    {
        $buttons = [];
        $perPage = 5;
        $totalEvents = count($events);
        $totalPages = (int)ceil($totalEvents / $perPage);

        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $eventsOnPage = array_slice($events, $offset, $perPage);

        // Add event buttons
        foreach ($eventsOnPage as $event) {
            $buttons[] = [
                ['text' => $event->getName(), 'callback_data' => 'manage_event_select_' . $event->getId()]
            ];
        }

        // Add pagination row if needed
        if ($totalPages > 1) {
            $paginationRow = [];

            if ($page > 1) {
                $paginationRow[] = ['text' => '⬅', 'callback_data' => 'manage_event_page_' . ($page - 1)];
            }

            $paginationRow[] = ['text' => "{$page}/{$totalPages}", 'callback_data' => 'manage_event_page_current'];

            if ($page < $totalPages) {
                $paginationRow[] = ['text' => '➡', 'callback_data' => 'manage_event_page_' . ($page + 1)];
            }

            $buttons[] = $paginationRow;
        }

        // Add back button
        $buttons[] = [
            ['text' => '⬅ Вернуться в меню', 'callback_data' => 'manage_event_back_to_events_menu'],
        ];

        return new InlineKeyboardMarkup($buttons);
    }

    public function sendEventDetailsMessage(int $chatId, Event $event): int
    {
        $text = $this->formatEventDetails($event);

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✏️ Редактировать', 'callback_data' => 'manage_event_edit'],
                ['text' => $event->isActive() ? '🔴 Деактивировать' : '🟢 Активировать', 'callback_data' => 'manage_event_toggle_active'],
            ],
            [
                ['text' => '🗑️ Удалить', 'callback_data' => 'manage_event_delete'],
            ],
            [
                ['text' => '⬅ К списку событий', 'callback_data' => 'manage_event_back_to_list'],
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

    public function editEventDetailsMessage(int $chatId, int $messageId, Event $event): void
    {
        $text = $this->formatEventDetails($event);

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✏️ Редактировать', 'callback_data' => 'manage_event_edit'],
                ['text' => $event->isActive() ? '🔴 Деактивировать' : '🟢 Активировать', 'callback_data' => 'manage_event_toggle_active'],
            ],
            [
                ['text' => '🗑️ Удалить', 'callback_data' => 'manage_event_delete'],
            ],
            [
                ['text' => '⬅ К списку событий', 'callback_data' => 'manage_event_back_to_list'],
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

    private function formatEventDetails(Event $event): string
    {
        $lines = ["*Детали события*\n"];

        $lines[] = "Название: *{$event->getName()}*";
        $lines[] = "Категория: *{$event->getType()->getName()}*";
        $lines[] = "Дата начала: *{$event->getPeriodFrom()->format('d-m-Y H:i')}*";
        $lines[] = "Дата окончания: *{$event->getPeriodTo()->format('d-m-Y H:i')}*";
        $lines[] = "Статус: *" . ($event->isActive() ? '🟢 Активно' : '🔴 Неактивно') . "*";

        if ($event->getEventGroup()) {
            $lines[] = "Группа: *{$event->getEventGroup()->getTitle()}*";
        }

        if ($event->getPartnerChanelLink()) {
            $lines[] = "Ссылка партнера: *{$event->getPartnerChanelLink()}*";
        }

        return implode("\n", $lines);
    }

    public function editToDeleteConfirmation(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*⚠️ Подтверждение удаления*\n\nВы уверены, что хотите удалить это событие?";

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '✅ Да, удалить', 'callback_data' => 'manage_event_confirm_delete'],
                ['text' => '❌ Отмена', 'callback_data' => 'manage_event_cancel_delete'],
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

    public function editToEditMenu(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*✏️ Редактирование*\n\nВыберите, что хотите изменить:";

        $buttons = [
            [
                ['text' => 'Название', 'callback_data' => 'manage_event_edit_title'],
            ],
            [
                ['text' => 'Дата начала', 'callback_data' => 'manage_event_edit_start_date'],
                ['text' => 'Дата окончания', 'callback_data' => 'manage_event_edit_end_date'],
            ],
            [
                ['text' => 'Группа', 'callback_data' => 'manage_event_edit_group'],
            ]
        ];

        // Add partner link button only for Event type
        if ($event->getType()->getName() === 'Ивент') {
            $buttons[] = [
                ['text' => 'Ссылка партнера', 'callback_data' => 'manage_event_edit_partner_link'],
            ];
        }

        $buttons[] = [
            ['text' => '⬅ Назад', 'callback_data' => 'manage_event_back_to_details'],
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

    public function editToTitleEdit(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*📝 Редактирование названия*\n\nВведите новое название события:";

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '❌ Отмена', 'callback_data' => 'manage_event_back_to_edit_menu'],
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

    public function editToStartDateEdit(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*📅 Редактирование даты начала*\n\nВведите новую дату и время начала события в формате: дд-мм-гггг чч:мм\nНапример: 01-02-2026 10:00";

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '❌ Отмена', 'callback_data' => 'manage_event_back_to_edit_menu'],
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

    public function editToEndDateEdit(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*📅 Редактирование даты окончания*\n\nВведите новую дату и время окончания события в формате: дд-мм-гггг чч:мм\nНапример: 21-02-2026 18:00";

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '❌ Отмена', 'callback_data' => 'manage_event_back_to_edit_menu'],
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

    public function editToPartnerLinkEdit(int $chatId, int $messageId, Event $event): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*🔗 Редактирование ссылки партнера*\n\nВведите новую ссылку на партнерский канал:";

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Удалить ссылку', 'callback_data' => 'manage_event_remove_partner_link'],
            ],
            [
                ['text' => '❌ Отмена', 'callback_data' => 'manage_event_back_to_edit_menu'],
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

    public function editToGroupSelection(int $chatId, int $messageId, Event $event, array $groups, int $page = 1): void
    {
        $detailsText = $this->formatEventDetails($event);

        $text = $detailsText . "\n\n*👥 Изменение группы*\n\nВыберите новую группу для проведения события:";

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

    public function sendEditGroupMessage(int $chatId, array $groups, int $page = 1): int
    {
        $text = <<<MARKDOWN
        *Изменение группы события*

        Выберите новую группу для проведения события:
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

    public function editEditGroupMessage(int $chatId, int $messageId, array $groups, int $page = 1): void
    {
        $text = <<<MARKDOWN
        *Изменение группы события*

        Выберите новую группу для проведения события:
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
                ['text' => $group->getTitle(), 'callback_data' => 'manage_event_select_group_' . $group->getId()]
            ];
        }

        // Add pagination row if needed
        if ($totalPages > 1) {
            $paginationRow = [];

            if ($page > 1) {
                $paginationRow[] = ['text' => '⬅', 'callback_data' => 'manage_event_group_page_' . ($page - 1)];
            }

            $paginationRow[] = ['text' => "{$page}/{$totalPages}", 'callback_data' => 'manage_event_group_page_current'];

            if ($page < $totalPages) {
                $paginationRow[] = ['text' => '➡', 'callback_data' => 'manage_event_group_page_' . ($page + 1)];
            }

            $buttons[] = $paginationRow;
        }

        // Add cancel button
        $buttons[] = [
            ['text' => '❌ Отмена', 'callback_data' => 'manage_event_close_group_selection'],
        ];

        return new InlineKeyboardMarkup($buttons);
    }

    public function sendErrorMessage(int $chatId, string $text): int
    {
        $message = $this->bot->sendMessage($chatId, $text, TelegramParseMode::MARKDOWN);

        return $message->getMessageId();
    }
}
