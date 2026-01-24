<?php

namespace App\Service\Telegram\Admin\Command\Context;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\Service\AdminMenuService;
use App\Service\Telegram\Admin\Service\ManageEventService;
use App\Service\Telegram\Context\Dto\ManageEventContext;
use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\TelegramBotService;

readonly class ManageEventCommandHandler
{
    use AnswerCallbackQueryTrait;

    public function __construct(
        private ManageEventService $manageEventService,
        private AdminMenuService $adminMenuService,
        private TelegramBotService $bot,
    ) {
    }

    public function handleCommand(AbstractPayload $payload, ManageEventContext $context): void
    {
        switch (true) {
            case $payload instanceof MessageTelegramPayload:
                $this->handleMessage($payload, $context);
                break;
            case $payload instanceof CallbackQueryTelegramPayload:
                $this->handleCallback($payload, $context);
                break;
        }
    }

    private function handleMessage(MessageTelegramPayload $payload, ManageEventContext $context): void
    {
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();
        $text = $payload->getText();

        // Emergency exit - /start closes context and returns to admin menu
        if ($text === '/start') {
            $this->manageEventService->emergencyExit($chatId, $messageId);
            return;
        }

        if ($context->getEditField() === 'start_date') {
            $this->manageEventService->updateStartDateAction($chatId, $messageId, $context, $text);
            return;
        }

        if ($context->getEditField() === 'end_date') {
            $this->manageEventService->updateEndDateAction($chatId, $messageId, $context, $text);
            return;
        }

        if ($context->getEditField() === 'partner_link') {
            $this->manageEventService->updatePartnerLinkAction($chatId, $messageId, $context, $text);
            return;
        }
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload, ManageEventContext $context): void
    {
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();

        // Handle pagination first (to avoid conflicts with similar prefixes)
        if (str_starts_with($data, 'manage_event_page_')) {
            $page = (int)str_replace('manage_event_page_', '', $data);
            if ($page > 0) {
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->eventPageAction($chatId, $messageId, $page);
            } else {
                $this->answerCallbackQuery($callbackId);
            }
            return;
        }

        // Handle group pagination
        if (str_starts_with($data, 'manage_event_group_page_')) {
            $page = (int)str_replace('manage_event_group_page_', '', $data);
            if ($page > 0) {
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->groupPageAction($chatId, $messageId, $context, $page);
            } else {
                $this->answerCallbackQuery($callbackId);
            }
            return;
        }

        // Handle event selection
        if (str_starts_with($data, 'manage_event_select_')) {
            $eventId = (int)str_replace('manage_event_select_', '', $data);
            $this->answerCallbackQuery($callbackId, 'событие выбрано');
            $this->manageEventService->selectEventAction($chatId, $messageId, $eventId);
            return;
        }

        // Handle group selection
        if (str_starts_with($data, 'manage_event_select_group_')) {
            $groupId = (int)str_replace('manage_event_select_group_', '', $data);
            $this->answerCallbackQuery($callbackId, 'группа выбрана');
            $this->manageEventService->selectGroupAction($chatId, $context, $groupId);
            return;
        }

        // Handle other actions
        switch ($data) {
            case 'manage_event_toggle_active':
                $this->answerCallbackQuery($callbackId, 'статус изменен');
                $this->manageEventService->toggleActiveAction($chatId, $messageId, $context);
                break;
            case 'manage_event_delete':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->showDeleteConfirmationAction($chatId, $context);
                break;
            case 'manage_event_confirm_delete':
                $this->answerCallbackQuery($callbackId, 'событие удалено');
                $this->manageEventService->confirmDeleteAction($chatId, $messageId, $context);
                break;
            case 'manage_event_cancel_delete':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->cancelDeleteAction($chatId, $messageId, $context);
                break;
            case 'manage_event_edit':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->showEditMenuAction($chatId, $context);
                break;
            case 'manage_event_edit_start_date':
                $this->answerCallbackQuery($callbackId, 'введите новую дату начала');
                $this->manageEventService->editStartDateAction($chatId, $context);
                break;
            case 'manage_event_edit_end_date':
                $this->answerCallbackQuery($callbackId, 'введите новую дату окончания');
                $this->manageEventService->editEndDateAction($chatId, $context);
                break;
            case 'manage_event_edit_partner_link':
                $this->answerCallbackQuery($callbackId, 'введите новую ссылку');
                $this->manageEventService->editPartnerLinkAction($chatId, $context);
                break;
            case 'manage_event_edit_group':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->editGroupAction($chatId, $context);
                break;
            case 'manage_event_remove_partner_link':
                $this->answerCallbackQuery($callbackId, 'ссылка удалена');
                $this->manageEventService->removePartnerLinkAction($chatId, $context);
                break;
            case 'manage_event_back_to_list':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->backToListAction($chatId, $messageId);
                break;
            case 'manage_event_back_to_details':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->backToDetailsAction($chatId, $context);
                break;
            case 'manage_event_back_to_edit_menu':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->backToEditMenuAction($chatId, $context);
                break;
            case 'manage_event_back_to_events_menu':
                $this->answerCallbackQuery($callbackId);
                $this->manageEventService->backToEventsMenuAction($chatId, $messageId, $this->adminMenuService);
                break;
            default:
                $this->answerCallbackQuery($callbackId);
                break;
        }
    }
}
