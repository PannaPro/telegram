<?php

namespace App\Service\Telegram\Subscription;

use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Menu\MenuService;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('action')]
class SubscriptionService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private MenuService $menuService,
        private TelegramMessageCache $cache,
        private StartService $startService,
        private LoggerInterface $logger,
    ) {
    }

    public function handleCallbackQueryPayload(int $chatId): void
    {
        $hasSubscription = $this->check($chatId);

        if ($hasSubscription) {
            $this->startService->handle();
        } else {
            $this->needSubscription($chatId);
        }
    }

    public function needSubscription(int $chatId): void
    {
        // TODO temporary
        if ($chatId < 0) {
            $this->logger->debug("Попытка отправит сообщение о подписке на канал $chatId");
            return;
        }

        $this->menuService->needChanelSubscribe($chatId);
    }

    public function check(int $chatId): bool
    {
        $type = 'subscription';
        $cached = $this->cache->get($type, $chatId);
        if ($cached == true) {
            return true;
        }

        $hasSubscription = $this->telegramBotService->isSubscribed($chatId);

        $this->cache->setEx($type, $chatId, 600, $hasSubscription);

        return $hasSubscription;
    }
}
