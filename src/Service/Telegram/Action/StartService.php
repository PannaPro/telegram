<?php

namespace App\Service\Telegram\Action;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Menu\MenuService;
use App\Service\Telegram\Subscription\SubscriptionService;

class StartService
{
    public function __construct(
        private MenuService $menuService,
        private SecurityTelegramUserService $security,
        private SubscriptionService $subscriptionService,
    ) {
    }

    public function handle(int $messageId = 0): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        if (!$this->subscriptionService->check($chatId)) {
            $this->subscriptionService->needSubscription($chatId);

            return;
        }

        if ($user->isParticipant()) {
            $this->menuService->sendStartMenu($chatId, $messageId);
            return;
        }

        $this->menuService->sendPreview($chatId);
    }
}
