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
            case 5: // Partner link (optional)
                $this->createEventService->partnerLinkAction($chatId, $messageId, $context, $text);
                break;
        }
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload, CreateEventContext $context): void
    {
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();

        // Parse callback data
        if (str_starts_with($data, 'create_event_type_')) {
            $eventTypeId = (int)str_replace('create_event_type_', '', $data);
            $this->createEventService->selectEventTypeAction($chatId, $callbackId, $context, $eventTypeId);
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
