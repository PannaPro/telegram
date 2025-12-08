<?php

namespace App\Service\Telegram\Subscription;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

#[WithMonologChannel('action')]
class SubscriptionService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramMessageCache $cache,
        private LoggerInterface $logger,
    ) {
    }

    public function handleCallbackQueryPayload(int $chatId): bool
    {
        $hasSubscription = $this->check($chatId);

        if (!$hasSubscription) {
            $this->needChanelSubscribe($chatId);
        }

        return $hasSubscription;
    }

    public function needSubscription(int $chatId): void
    {
        // TODO temporary
        if ($chatId < 0) {
            $this->logger->debug("Попытка отправить сообщение о подписке на канал $chatId");
            return;
        }

        $this->needChanelSubscribe($chatId);
    }

    private function needChanelSubscribe(int $chatId): void
    {
        $text = <<<MARKDOWN
            👋 *Привет, дорогой друг!*

            Чтобы пользоваться игровым ботом нужна подписка на наш [канал](https://t.me/PAKETAGAME?start=1)
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Проверить подписку', 'callback_data' => 'subscription']
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $message->getMessageId());
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
    }

    public function check(int $chatId): bool
    {
        $cached = $this->cache->get(TelegramCacheKey::SUBSCRIPTION, $chatId);
        if ($cached == true) {
            return true;
        }

        $hasSubscription = $this->telegramBotService->isSubscribed($chatId);

        $this->cache->setEx(TelegramCacheKey::SUBSCRIPTION, $chatId, TelegramCacheKey::TTL_10_MINUTES, $hasSubscription);

        return $hasSubscription;
    }


}
