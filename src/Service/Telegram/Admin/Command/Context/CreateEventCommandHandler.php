<?php

namespace App\Service\Telegram\Admin\Command\Context;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\Service\CreateEventService;
use App\Service\Telegram\Context\Dto\CreateEventContext;

class CreateEventCommandHandler
{
    public function __construct(
        private CreateEventService $createEventService,
    ) {
    }

    public function handleCommand(AbstractPayload $payload, CreateEventContext $context): void
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

    private function handleMessage(MessageTelegramPayload $payload, CreateEventContext $context): void
    {
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();
        $text = $payload->getText();

        // Emergency exit - /start closes context and returns to admin menu
        if ($text === '/start') {
            $this->createEventService->emergencyExit($chatId, $messageId);
            return;
        }

        switch ($context->getStep()) {
            case 2: // Event name
                $this->createEventService->eventNameAction($chatId, $messageId, $context, $text);
                break;
            case 3: // Event start date
                $this->createEventService->eventStartDateAction($chatId, $messageId, $context, $text);
                break;
            case 4: // Event end date
                $this->createEventService->eventEndDateAction($chatId, $messageId, $context, $text);
                break;
            case 5: // Group selection (handled via callbacks)
                // No text input on this step
                break;
            case 6: // Partner link (optional, only for Event type)
                $this->createEventService->partnerLinkAction($chatId, $messageId, $context, $text);
                break;
        }
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload, CreateEventContext $context): void
    {
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();

        // Parse callback data - ORDER MATTERS! Check page before group
        if (str_starts_with($data, 'create_event_type_')) {
            $eventTypeId = (int)str_replace('create_event_type_', '', $data);
            $this->createEventService->selectEventTypeAction($chatId, $callbackId, $context, $eventTypeId);
            return;
        }

        if (str_starts_with($data, 'create_event_group_page_')) {
            $pageStr = str_replace('create_event_group_page_', '', $data);
            if ($pageStr !== 'current') {
                $page = (int)$pageStr;
                $this->createEventService->groupPageAction($chatId, $callbackId, $context, $page);
            }
            return;
        }

        if (str_starts_with($data, 'create_event_group_')) {
            $groupId = (int)str_replace('create_event_group_', '', $data);
            $this->createEventService->selectGroupAction($chatId, $callbackId, $context, $groupId);
            return;
        }

        switch ($data) {
            case 'create_event_back_to_type':
                $this->createEventService->backToTypeAction($chatId, $callbackId, $context);
                break;
            case 'create_event_back_to_name':
                $this->createEventService->backToNameAction($chatId, $callbackId, $context);
                break;
            case 'create_event_back_to_start_date':
                $this->createEventService->backToStartDateAction($chatId, $callbackId, $context);
                break;
            case 'create_event_back_to_end_date':
                $this->createEventService->backToEndDateAction($chatId, $callbackId, $context);
                break;
            case 'create_event_back_to_group_selection':
                $this->createEventService->backToGroupSelectionAction($chatId, $callbackId, $context);
                break;
            case 'create_event_skip_partner_link':
                $this->createEventService->skipPartnerLinkAction($chatId, $callbackId, $context);
                break;
            case 'create_event_confirm':
                $this->createEventService->confirmEventAction($chatId, $callbackId, $context);
                break;
            case 'create_event_edit':
                $this->createEventService->editEventAction($chatId, $callbackId, $context);
                break;
            case 'create_event_cancel':
            case 'create_event_back_to_events_menu':
                $this->createEventService->cancelEventAction($chatId, $callbackId);
                break;
        }
    }
}
