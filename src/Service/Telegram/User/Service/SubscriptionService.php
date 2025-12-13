<?php

namespace App\Service\Telegram\User\Service;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\SubscriptionMessage;
use App\Service\Telegram\TelegramMessageCache;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('action')]
class SubscriptionService
{
    public function __construct(
        private LoggerInterface $logger,
        private SubscriptionMessage $message,
        private TelegramMessageCache $cache,
        private StartService $startService
    ) {
    }

    public function handleCallbackQueryPayload(int $chatId, string $callbackQueryId, int $messageId): void
    {
        $this->message->answerCallbackQuery($callbackQueryId);

        $hasSubscription = $this->check($chatId);
        if (!$hasSubscription) {
            $this->message->editNeedSubscription($chatId, $messageId);
            return;
        }

        $this->startService->makeAction($chatId);
    }

    public function needSubscription(int $chatId): void
    {
        if ($chatId < 0) {
            $this->logger->debug("Попытка отправить сообщение о подписке на канал $chatId");
            return;
        }

        $messageId = $this->message->sendNeedSubscription($chatId);

        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $messageId);
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }

    public function check(int $chatId): bool
    {
        $cached = $this->cache->get(TelegramCacheKey::SUBSCRIPTION, $chatId);
        if ($cached == true) {
            return true;
        }

        $hasSubscription = $this->message->checkSubscription($chatId);

        $this->cache->setEx(TelegramCacheKey::SUBSCRIPTION, $chatId, TelegramCacheKey::TTL_10_MINUTES, $hasSubscription);

        return $hasSubscription;
    }
}
