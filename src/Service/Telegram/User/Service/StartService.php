<?php

namespace App\Service\Telegram\User\Service;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;

class StartService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private MenuService $menuService,
    ) {
    }

    public function makeAction(DeletableTelegramMessageInterface $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        if ($user->isParticipant()) {
            $this->menuService->sendStartMenu($chatId, $currentMessage);

            return;
        }

        $this->menuService->sendPreview($chatId);
    }
}
