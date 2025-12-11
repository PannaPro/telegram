<?php

namespace App\Service\Telegram;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;
use App\Repository\TelegramUserRepository;
use App\Service\ExceptionHandle\NotFoundException;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class TelegramUserService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
    ) {
    }

    public function loadTelegramUser(AbstractPayload $dto): TelegramUser
    {
        /** TODO temporary */
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
