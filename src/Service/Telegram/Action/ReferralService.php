<?php

namespace App\Service\Telegram\Action;

use App\Entity\TelegramUser;
use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ReferralService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private SecurityTelegramUserService $security,
        private TelegramMessageCache $cache,
        private TelegramUserRepository $telegramUserRepository,
    ) {
    }

    public function handle(int $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        $referralCount = $this->telegramUserRepository->countReferrals($user->getId());

        $referralCode = $this->makeReferralCode($user);
        $text = rawurlencode("Привет! Нашел крутого бота где проводятся игры, а призы реальные NFT!");
        $botLink = "https://t.me/share?url=https://t.me/PAKETAGAME_bot?start=$referralCode&text=$text";

        $text = <<<MARKDOWN
        👥 *Ваши рефералы:*
        Всего приглашенных: *{$referralCount}*
        MARKDOWN;

        $inlineKeyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Пригласить', 'url' => $botLink],
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $inlineKeyboard
        );

        $this->cache->clear('step', $chatId, $currentMessage, $message->getMessageId());
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
