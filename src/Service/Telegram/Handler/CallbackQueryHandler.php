<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Action\AvatarSetService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Subscription\SubscriptionService;

class CallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
        private AvatarSetService $avatarSetService,
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService $subscriptionService,
        private StartService $startService,
    ) {
    }

    public function makeAction(CallbackQueryTelegramPayload $dto): void
    {
        $data = $dto->getCallbackData();
        $chatId = $dto->getChatId();

        switch ($data) {
            case 'subscription':
                /** Callback will send the repeat message if user hasn't subscription */
                if ($this->subscriptionService->handleCallbackQueryPayload($chatId)) {
                    $this->startService->handle();
                }
                break;
            case 'participate':
                $this->participateService->handleCallbackQuery();
                break;
            case 'avatarSet':
                $this->avatarSetService->handleCallbackQuery();
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
