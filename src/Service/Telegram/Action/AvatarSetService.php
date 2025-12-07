<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Menu\MenuService;

class AvatarSetService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private TelegramUserRepository $telegramUserRepository,
        private ParticipateService $participateService,
        private MenuService $menuService,
    ) {
    }

    public function handleCallbackQuery(int $messageId): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        if ($user->getUsername() === 'unknown') {
            $this->participateService->needUsernameMessage($chatId);
            return;
        }

        $user->setParticipant(true);
        $this->telegramUserRepository->save($user);

        $this->menuService->sendStartMenu($chatId, $messageId);
    }
}
