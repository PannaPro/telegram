<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Security\SecurityTelegramUserService;
use App\Security\AdminSessionService;
use App\Service\Telegram\MyChatMember\MyChatMemberService;
use App\Service\Telegram\Router\AdminPayloadRouter;
use App\Service\Telegram\Router\UserPayloadRouter;

readonly class PayloadHandler
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private MyChatMemberService $myChatMemberService,
        private AdminPayloadRouter $adminPayloadRouter,
        private UserPayloadRouter $userPayloadRouter,
        private AdminSessionService $adminSessionService,
    ) {
    }

    /** TODO повесить лок менеджер */
    public function handlePayload(AbstractPayload $payload): void
    {
        if ($payload instanceof MyChatMemberPayload) {
            $this->myChatMemberService->makeAction($payload);

            return;
        }

        // TODO
        if ($payload->getChatType() !== 'private') {
            return;
        }

        /** tODO добавиить канал Тест бота в май чат мембер */
        $this->security->setCurrentTelegramUser($payload);

//        $this->adminPayloadRouter->route($payload);

//        if ($this->adminSessionService->isAdminSessionActive()) {
//            $this->adminPayloadRouter->route($payload);
//            return;
//        }
//
        $this->userPayloadRouter->route($payload);
    }
}
