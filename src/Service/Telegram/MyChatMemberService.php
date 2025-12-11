<?php

namespace App\Service\Telegram;

use App\Entity\TelegramUser;
use App\Http\Dto\MyChatMemberPayload;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class MyChatMemberService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
    ) {
    }

    public function makeAction(MyChatMemberPayload $payload): void
    {
        $chatId = $payload->getChatId();
        $active = $payload->getNewChatMemberStatus() !== TelegramDefaultValue::KICKED;

        $user = $this->telegramUserRepository->findOneBy([TelegramDefaultValue::CHAT_ID => $chatId]);
        if ($user instanceof TelegramUser) {
            $user->setUsername($payload->getUsername());
            $user->setFirstName($payload->getFirstName());
            $user->setLastName($payload->getLastName());
            $user->setIsActive($active);
            $user->setParticipant(false);
        } else {
            $user = new TelegramUser();
            $user
                ->setChatId($chatId)
                ->setUsername($payload->getUsername())
                ->setFirstName($payload->getFirstName())
                ->setLastName($payload->getLastName());

            /** Set referralWindow on 5 minutes */
            $this->cache->setEx(TelegramCacheKey::REFERRAL_WINDOW, $chatId, TelegramCacheKey::TTL_5_MINUTES, 1);
        }

        $this->telegramUserRepository->save($user);
    }
}
