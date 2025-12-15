<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminReferralMessage;
use App\Service\Telegram\Message\AdminTopReferralMessage;
use App\Service\Telegram\TelegramMessageCache;

class AdminReferralService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
        private AdminTopReferralMessage $topReferralMessage,
        private AdminReferralMessage $referralMessage
    ) {
    }

    public function makeAction(int $chatId, int $currentMessage): void
    {
        $referrals = $this->telegramUserRepository->findReferralsStatistics();
        $messageId = $this->referralMessage->sendMessage($chatId, $referrals);

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }

    public function makeTopReferralAction(int $chatId, int $callbackId): void
    {
        $this->topReferralMessage->answerCallbackQuery($callbackId);
        $messageId = $this->topReferralMessage->sendMessage($chatId);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
