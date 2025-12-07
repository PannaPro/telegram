<?php

namespace App\Service\Telegram\Action;

use App\Entity\TelegramUser;
use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
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
    )
    {
    }

    public function handle(int $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        $referralCount = $this->telegramUserRepository->countReferrals($user->getId());

        $referralCode = $this->makeReferralCode($user);

//        $referralLink = "https://t.me/PAKETAGAME_bot?start=$referralCode";

        $text = "Привет! Нашел крутого бота где проводятся игры, а призы реальные NFT!";
        $textParam = urlencode("Привет! Нашел крутого бота со скидками на все популярные магазины");
//        $encodedText = str_replace('+', ' ', rawurlencode($textParam));

        $botLink = "https://t.me/share?url=https://t.me/PAKETAGAME_bot?start=$referralCode&text=$textParam";

//        $textParam = urlencode("Привет! Нашел крутого бота со скидками на все популярные магазины");
//        $encodedText = str_replace('+', ' ', rawurlencode($text));
//        $botLink = "https://t.me/share?url=https://t.me/PAKETAGAME_bot?start=$referralCode";

        $text = <<<MARKDOWN
        👥 *Ваши рефералы:*
        Всего приглашенных: *{$referralCount}*
        MARKDOWN;

        $inlineKeyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Пригласить', 'url' => $botLink],
            ],
            [
                ['text' => 'Закрыть', 'callback_data' => 'close_referrals']
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

        $this->cache->saveAndCleanup('step', $chatId, $message->getMessageId());
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
        if (empty($referralLink)) {
            return;
        }

        $referrer = $this->telegramUserRepository->findOneBy(['referralLink' => $referralLink]);
        if (!$referrer) {
            return;
        }

        $referred = $this->security->fetchCurrentUser();

        if ($referrer->getChatId() === $chatId) {
            return;
        }

        if ($referred->getReferrer() === null) {
            $referrer->addReferral($referred);
            $this->telegramUserRepository->save($referred);
        }
    }
}
