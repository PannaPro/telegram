<?php

namespace App\Service\Telegram\Admin\AdminAction\ReferralSearch;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\TelegramMessageCache;

class ReferralSearchStorage
{
    public function __construct(
        private TelegramMessageCache $cache,
    ) {
    }

    public function setReferralSearchContext(int $chatId, string $searchType): void
    {
        $context = new ReferralSearchContextDto($chatId, $searchType);
        $value = $context->toArray();

        $this->cache->set("admin_referral_search_context", $chatId, $value);
    }

    public function loadReferralSearchContext(int $chatId): ReferralSearchContextDto
    {
        $value = $this->cache->get("admin_referral_search_context", $chatId);
        if (!$value) {
            throw new \Exception("Контекст отсутствует");
        }


        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return ReferralSearchContextDto::fromArray($data[0]);
    }

    public function saveReferralSearchContext(int $chatId, ReferralSearchContextDto $context): void
    {
        $value = $context->toArray();

        $this->cache->set("admin_referral_search_context", $chatId, $value);
    }

    public function unsetReferralSearchContext(int $chatId): void
    {
        $this->cache->delete("admin_referral_search_context", $chatId);
    }

    public function setReferralSearchWaiting(int $chatId): void
    {
        $this->cache->setEx("admin_referral_search", $chatId, TelegramCacheKey::TTL_5_MINUTES, 1);
    }

    public function unsetReferralSearchWaiting(int $chatId): void
    {
        $this->unsetReferralSearchContext($chatId);
        $this->cache->delete("admin_referral_search", $chatId);
    }

    public function isReferralSearchWaiting(int $chatId): bool
    {
        return $this->cache->get("admin_referral_search", $chatId) == true;
    }
}
