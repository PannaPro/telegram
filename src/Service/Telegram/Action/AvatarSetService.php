<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Menu\MenuService;

class AvatarSetService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private ParticipateService $participateService,
        private MenuService $menuService,
    ) {
    }

    public function handleCallbackQuery(CallbackQueryTelegramPayload $dto): void
    {
        $chatId = $dto->getChatId();
        $user = $this->telegramUserRepository->findOneBy(['chatId' => $chatId]);

        if ($user->getUsername() === 'unknown') {
            $this->participateService->needUsernameMessage($chatId);
            return;
        }

        $user->setParticipant(true);
        $this->telegramUserRepository->save($user);

        $text = <<<MARKDOWN
        🎲 Ты в игре!
        MARKDOWN;

        $this->menuService->sendStartMenu($chatId, $text);
    }
}
