<?php

namespace App\Service\Telegram\Subscription;

use App\Service\Telegram\Menu\MenuService;
use App\Service\TelegramBotService;

class SubscriptionService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private MenuService $menuService,
    ) {
    }

    public function needSubscription(int $chatId): void
    {
        $this->menuService->needChanelSubscribe($chatId);
    }

    public function check(int $chatId): bool
    {
        return $this->telegramBotService->isSubscribed($chatId);
    }
}
