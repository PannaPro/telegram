<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Action\AvatarSetService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Subscription\SubscriptionService;

class CallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
        private AvatarSetService $avatarSetService,
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService $subscriptionService,
    ) {
    }

    public function makeAction(CallbackQueryTelegramPayload $dto): void
    {
        $data = $dto->getCallbackData();
        $messageId = $dto->getMessageId();

        switch ($data) {
            case 'participate':
                $this->participateService->handleCallbackQuery();
                break;
            case 'avatarSet':
                $this->avatarSetService->handleCallbackQuery($messageId);
                break;
            case 'subscription':
                $this->subscriptionService->handleCallbackQueryPayload($dto->getChatId(), $messageId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($dto);
        }
    }
}
