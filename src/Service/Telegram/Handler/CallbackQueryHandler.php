<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Action\AvatarSetService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Menu\MenuService;
use App\Service\Telegram\Subscription\SubscriptionService;

class CallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
        private AvatarSetService $avatarSetService,
        private UnknownCommandService $unknownCommandService,
        private StartService $startService,
    ) {
    }

    public function makeAction(CallbackQueryTelegramPayload $dto): void
    {
        $data = $dto->getCallbackData();

        switch ($data) {
            case 'participate':
                $this->participateService->handleCallbackQuery();
                break;
            case 'avatarSet':
                $this->avatarSetService->handleCallbackQuery();
                break;
            case 'subscription':
                $this->startService->handle();
            default:
                $this->unknownCommandService->handleCallbackQuery($dto);
        }
    }
}
