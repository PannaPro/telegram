<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Admin\AdminAction\AdminSessionService;
use App\Service\Telegram\Admin\Handler\AdminCallbackQueryHandler;
use App\Service\Telegram\Admin\Handler\AdminMessageHandler;
use App\Service\Telegram\MyChatMemberService;
use App\Service\Telegram\User\Handler\UserCallbackQueryHandler;
use App\Service\Telegram\User\Handler\UserMessageHandler;

class PayloadHandler
{
    public function __construct(
        private UserMessageHandler $userMessageHandler,
        private UserCallbackQueryHandler $callbackQueryHandler,
        private SecurityTelegramUserService $security,
        private AdminMessageHandler $adminMessageHandler,
        private AdminCallbackQueryHandler $adminCallbackQueryHandler,
        private AdminSessionService $adminSessionService,
        private MyChatMemberService $myChatMemberService,
    ) {
    }

    public function handlePayload(AbstractPayload $payload): void
    {
        if ($payload instanceof MyChatMemberPayload) {
            $this->myChatMemberService->makeAction($payload);

            return;
        }

        $user = $this->security->setCurrentTelegramUser($payload);
        $chatId = $user->getChatId();

        $isWaitingPassword = $this->adminSessionService->isWaitingPassword($chatId);
        $isAdminSession = $this->adminSessionService->isActiveAdminSession($chatId);
        if ($user->isAdmin() && ($isWaitingPassword || $isAdminSession)) {
            $this->routeAdminPayload($payload);
            return;
        }

        $this->routeUserPayload($payload);
    }

    private function routeUserPayload(AbstractPayload $payload): void
    {
        match (true) {
            $payload instanceof MessageTelegramPayload => $this->userMessageHandler->makeAction($payload),
            $payload instanceof CallbackQueryTelegramPayload => $this->callbackQueryHandler->makeAction($payload),
            default => null,
        };
    }

    private function routeAdminPayload(AbstractPayload $payload): void
    {
        match (true) {
            $payload instanceof MessageTelegramPayload => $this->adminMessageHandler->makeAction($payload),
            $payload instanceof CallbackQueryTelegramPayload => $this->adminCallbackQueryHandler->makeAction($payload),
            default => null,
        };
    }
}
