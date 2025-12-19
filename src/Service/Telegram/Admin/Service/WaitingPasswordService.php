<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\WaitingPasswordContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\IncorrectPasswordMessage;
use App\Service\Telegram\Message\WaitingPasswordMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\Telegram\User\Service\StartService;
use App\Service\TelegramBotService;

class WaitingPasswordService
{
    public function __construct(
        private TelegramBotService $bot,
        private TelegramMessageCache $cache,
        private ContextStorage $contextStorage,
        private WaitingPasswordMessage $waitingPasswordMessage,
        private IncorrectPasswordMessage $incorrectPasswordMessage,
    ) {
    }

    public function waitingPassword(int $chatId, int $currentMessage): void
    {
        $this->cache->deleteMessage($chatId, $currentMessage);
        $messageId = $this->waitingPasswordMessage->sendMessage($chatId);

        $this->contextStorage->setContext($chatId, new WaitingPasswordContext($chatId, true), TelegramCacheKey::TTL_5_MINUTES);
        $this->cache->deletePreviousMessage(TelegramCacheKey::START_MENU, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function incorrectPassword(int $chatId, int $currentMessage): void
    {
        $messageId = $this->incorrectPasswordMessage->sendMessage($chatId);

        $this->cache->deleteMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }
}
