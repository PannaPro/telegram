<?php

namespace App\Service\Telegram\MyChatMember;

use App\Entity\TelegramEventGroup;
use App\Http\Dto\MyChatMemberPayload;
use App\Repository\TelegramEventGroupRepository;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class TelegramGroupService
{
    public function __construct(
        private TelegramEventGroupRepository $repository,
    ) {}

    public function handle(MyChatMemberPayload $payload): void
    {
        if (!$payload->isGroup()) {
            return;
        }

        $chatId = $payload->getChatId();

        $group = $this->repository->findOneBy(['chatId' => $chatId]);

        if (!$group) {
            $group = new TelegramEventGroup();
            $group->setChatId($chatId);
            $group->setTitle($payload->getChatTitle());
            $group->setType($payload->getChatType());
        }

        $newStatus = $payload->getNewChatMemberStatus();

        if ($newStatus === TelegramDefaultValue::ADMINISTRATOR) {
            $group->setBotIsAdmin(true);
            $group->setCanInvite($payload->canInviteUsers());
            $group->setIsActive(true);
        }

        if (
            $newStatus === TelegramDefaultValue::MEMBER
            || $newStatus === TelegramDefaultValue::LEFT
            || $newStatus === TelegramDefaultValue::KICKED
        ) {
            $group->setBotIsAdmin(false);
            $group->setCanInvite(false);
            $group->setIsActive(false);
        }

        $this->repository->save($group);
    }
}
