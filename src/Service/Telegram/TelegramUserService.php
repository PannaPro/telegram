<?php

namespace App\Service\Telegram;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Repository\TelegramUserRepository;
use App\Service\ExceptionHandle\NotFoundException;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class TelegramUserService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
    ) {
    }

    public function loadTelegramUser(AbstractPayload $dto): TelegramUser
    {
        if ($dto instanceof MyChatMemberPayload) {
            $user = $this->loadOrUpdateTelegramUser($dto);
        } else {
            $user = $this->fetchCurrentUser($dto);
        }

        return $user;
    }

    private function loadOrUpdateTelegramUser(MyChatMemberPayload $dto): TelegramUser
    {
        $chatId = $dto->getChatId();
        $active = $dto->getNewChatMemberStatus() !== TelegramDefaultValue::KICKED;

        $user = $this->telegramUserRepository->findOneBy([TelegramDefaultValue::CHAT_ID => $chatId]);
        if ($user instanceof TelegramUser) {
            $user->setUsername($dto->getUsername());
            $user->setFirstName($dto->getFirstName());
            $user->setLastName($dto->getLastName());
            $user->setIsActive($active);
            $user->setParticipant(false);
        } else {
            $user = new TelegramUser();
            $user
                ->setChatId($chatId)
                ->setUsername($dto->getUsername())
                ->setFirstName($dto->getFirstName())
                ->setLastName($dto->getLastName());

            /** Set referralWindow on 5 minutes */
            $this->cache->setEx(TelegramCacheKey::REFERRAL_WINDOW, $chatId, TelegramCacheKey::TTL_5_MINUTES, 1);
        }

        $this->telegramUserRepository->save($user);

        return $user;
    }

    private function fetchCurrentUser(AbstractPayload $dto): TelegramUser
    {
        $user = $this->telegramUserRepository->findOneBy([TelegramDefaultValue::CHAT_ID => $dto->getChatId()]);
        if ($user instanceof TelegramUser) {
            $user->setUsername($dto->getUsername());
            $user->setFirstName($dto->getFirstName());
            $user->setLastName($dto->getLastName());
        } else {
            throw NotFoundException::userNotFound();
        }

        $this->telegramUserRepository->save($user);

        return $user;
    }
}
