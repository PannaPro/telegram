<?php

namespace App\Service\Telegram\NewChatTitle;

use App\Http\Dto\NewChatTitleTelegramPayload;
use App\Repository\TelegramEventGroupRepository;

class NewChatTitleService
{
    public function __construct(
        private TelegramEventGroupRepository $repository,
    )
    {
    }

    public function makeAction(NewChatTitleTelegramPayload $payload): void
    {
        $groupChatId = $payload->getChatId();

        $entity = $this->repository->findOneBy(['chatId' => $groupChatId]);
        if (empty($entity)) {
            return;
        }

        $entity->setTitle($payload->getNewTitle());
        $this->repository->save($entity);
    }
}
