<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminReferralMessage;
use App\Service\Telegram\TelegramMessageCache;

class AdminReferralService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
        private AdminReferralMessage $message,
    ) {
    }

    public function makeAction(int $chatId, int $currentMessage): void
    {
        $referrals = $this->telegramUserRepository->findReferralsStatistics();

        $messageId = $this->message->sendMessage($chatId, $referrals);

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }
}
