<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminReferralMessage;
use App\Service\Telegram\Message\AdminReferralSearchMessage;
use App\Service\Telegram\TelegramMessageCache;

class AdminReferralService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
        private AdminReferralMessage $referralMessage,
    ) {
    }

    public function makeAction(int $chatId, int $currentMessage = 0): void
    {
        $referrals = $this->telegramUserRepository->findReferralsStatistics();
        $messageId = $this->referralMessage->sendMessage($chatId, $referrals);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }

    public function search(ReferralSearchContext $context): void
    {
        $chatId = $context->getChatId();

        $result = [];
        $textHeader = $context->getTextType();
        $contextMessage = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $messageId = $this->referralMessage->sendReferralSearchResult($contextMessage, $result, $textHeader);

        $this->cache->cleanup(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
