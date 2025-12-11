<?php

namespace App\Service\Telegram\User\Handler;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Admin\AdminAction\AdminSessionService;
use App\Service\Telegram\User\Action\AvatarSetService;
use App\Service\Telegram\UnknownCommandService;
use App\Service\Telegram\User\Action\ParticipateService;
use App\Service\Telegram\User\Action\StartService;
use App\Service\Telegram\User\Action\SubscriptionService;

class UserCallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
        private AvatarSetService $avatarSetService,
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService $subscriptionService,
        private StartService $startService,
        private AdminSessionService $activateSessionService,
    ) {
    }

    public function makeAction(CallbackQueryTelegramPayload $dto): void
    {
        $data = $dto->getCallbackData();
        $chatId = $dto->getChatId();
        $messageId = $dto->getMessageId();

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
            case 'close_password_menu':
                $this->activateSessionService->deactivate($chatId, $messageId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
