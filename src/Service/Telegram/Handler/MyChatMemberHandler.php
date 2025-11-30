<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Repository\TelegramUserRepository;

class MyChatMemberHandler
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
    ) {
    }

    /**
     * @param MyChatMemberPayload $dto
     * @return void
     */
    public function makeAction(AbstractPayload $dto): void
    {
        $chatId = $dto->getChatId();
        $newStatus = $dto->getNewChatMemberStatus();

        $user = $this->telegramUserRepository->findOneBy(['chatId' => $chatId]);
        if (!$user) {
            return;
        }

        $status = !($newStatus === 'kicked');
        $user->setIsActive($status);

        $this->telegramUserRepository->save($user);
    }
}
