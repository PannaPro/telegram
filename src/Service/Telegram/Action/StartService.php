<?php

namespace App\Service\Telegram\Action;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Menu\MenuService;

class StartService
{
    public function __construct(
        private MenuService $menuService,
        private SecurityTelegramUserService $security,
    ) {
    }

    public function handle(): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        if ($user->isParticipant()) {
            $this->menuService->sendStartMenu($chatId);
            return;
        }

        $this->menuService->sendPreview($chatId);
    }
}
