<?php

namespace App\Service\Telegram\User\Command;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Common\CommonActionService;
use App\Service\Telegram\Common\UnknownCommandService;
use App\Service\Telegram\User\Service\AvatarService;
use App\Service\Telegram\User\Service\ParticipateService;
use App\Service\Telegram\User\Service\SubscriptionService;

class UserCommandCallbackQueryHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService   $subscriptionService,
        private ParticipateService    $participateService,
        private AvatarService         $avatarService,
        private CommonActionService $commonActionService,
    ) {
    }

    public function handleCommand(CallbackQueryTelegramPayload $payload): void
    {
        $data = $payload->getCallbackData();
        $callbackId = $payload->getCallbackQueryId();
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();

        switch ($data) {
            case 'subscription':
                /** Callback will send the repeat message if user hasn't subscription */
                $this->subscriptionService->handleCallbackQueryPayload($chatId, $callbackId, $messageId);
                break;
            case 'participate':
                $this->participateService->handleCallbackQuery($callbackId);
                break;
            case 'avatarSet':
                $this->avatarService->handleCallbackQuery($callbackId);
                break;
            case 'close_pinned_message':
                $this->commonActionService->deletePinnedMessage($chatId, $callbackId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
