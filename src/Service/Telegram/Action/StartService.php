<?php

namespace App\Service\Telegram\Action;

use App\Entity\TelegramUser;
use App\Http\Dto\MessageTelegramPayload;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Menu\MenuService;

class StartService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private MenuService $menuService,
    ) {
    }

    public function handle(MessageTelegramPayload $dto): void
    {
        $chatId = $dto->getChatId();
        $username = $dto->getUsername();

        $user = $this->telegramUserRepository->findOneBy(['chatId' => $chatId]);
        if ($user instanceof TelegramUser) {
            $user->setUsername($username);
            $user->setIsActive(true);
        } else {
            $user = new TelegramUser();
            $user
                ->setUsername($username)
                ->setChatId($chatId)
                ->setFirstName($dto->getFirstName())
                ->setLastName($dto->getLastName());
        }

        $this->telegramUserRepository->save($user);

        if ($user->isParticipant()) {
            $this->menuService->sendStartMenu($chatId);
        } else {
            $this->menuService->sendPreview($chatId);
        }
    }
}
