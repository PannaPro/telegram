<?php

namespace App\Service\Telegram\User\Service;

use App\Security\SecurityTelegramUserService;

class StartService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private MenuService $menuService,
    ) {
    }

    public function makeAction(int $messageId = 0): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        if ($user->isParticipant()) {
            $this->menuService->sendStartMenu($chatId);

            return;
        }

        $this->menuService->sendPreview($chatId, $messageId);
    }
}
