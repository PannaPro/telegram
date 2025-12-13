<?php

namespace App\Service\Telegram\User\Service;

use App\Entity\TelegramUser;
use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\Message\ReferralMessage;
use App\Service\Telegram\TelegramMessageCache;

class ReferralService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private TelegramMessageCache $cache,
        private TelegramUserRepository $telegramUserRepository,
        private ReferralMessage $referralMessage,
    ) {
    }

    public function makeAction(int $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        $referralCode = $this->makeReferralCode($user);
        $referralCount = $this->telegramUserRepository->countReferrals($user->getId());

        $messageId = $this->referralMessage->sendMessage($chatId, $referralCode, $referralCount);

        $this->cache->clear('step', $chatId, $currentMessage, $messageId);
    }

    private function makeReferralCode(TelegramUser $user): string
    {
        $link = $user->getReferralLink();
        if ($link === 'unknown') {
            $link = $this->generateCode($user->getId());
            $user->setReferralLink($link);
            $this->telegramUserRepository->save($user);
        }

        return $link;
    }

    private function generateCode(int $userId): string
    {
        $hash = md5($userId . "@PAKETAGAME");

        return substr($hash, 0, 10);
    }

    public function addReferral(int $chatId, string $referralLink): void
    {
        if ($referralLink === TelegramDefaultValue::UNKNOWN) {
            return;
        }

        /** Only first 5 minute allows to set referrer */
        $referralWindow = $this->cache->get(TelegramCacheKey::REFERRAL_WINDOW, $chatId);
        if ($referralWindow === false) {
            return;
        }

        $referred = $this->security->fetchCurrentUser();
        if ($referred->getReferrer() !== null) {
            return;
        }

        $referrer = $this->telegramUserRepository->findOneBy(['referralLink' => $referralLink]);
        if (!$referrer || $referrer->getChatId() === $chatId) {
            return;
        }

        $referrer->addReferral($referred);
        $this->telegramUserRepository->save($referred);
    }
}
