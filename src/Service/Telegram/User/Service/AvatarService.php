<?php

namespace App\Service\Telegram\User\Service;

use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\TelegramBotService;

class AvatarService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private TelegramUserRepository $telegramUserRepository,
        private ParticipateService $participateService,
        private MenuService $menuService,
        private TelegramBotService $bot,
    ) {
    }

    public function handleCallbackQuery(int $callbackQueryId): void
    {
        $this->bot->answerCallbackQuery($callbackQueryId);

        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        if ($user->getUsername() === TelegramDefaultValue::UNKNOWN) {
            $this->participateService->needUsernameMessage($chatId);
            return;
        }

        $user->setParticipant(true);
        $this->telegramUserRepository->save($user);

        $this->menuService->sendStartMenu($chatId);
    }
}
