<?php

namespace App\Service\Telegram\Admin\Handler;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\AdminAction\AdminReferralService;
use App\Service\Telegram\Admin\AdminAction\AdminSessionService;
use App\Service\Telegram\UnknownCommandService;
use App\Service\Telegram\Admin\AdminAction\AdminMenuService;

class AdminMessageHandler
{
    public function __construct(
        private AdminMenuService $adminMenuService,
        private AdminSessionService $activateSessionService,
        private AdminReferralService $adminReferralService,
        private UnknownCommandService $unknownCommandService,
    ) {
    }

    public function makeAction(MessageTelegramPayload $dto): void
    {
        $text = $dto->getText();
        $messageId = $dto->getMessageId();
        $chatId = $dto->getChatId();

        if ($this->activateSessionService->isWaitingPassword($dto->getChatId())) {
            $this->activateSessionService->handleAdminPassword($chatId, $text);
            return;
        }

        switch ($text) {
            case '/start':
            case 'Вернуться в меню':
                $this->adminMenuService->handle($chatId, $messageId);
                $this->activateSessionService->activateAdminSession($chatId);
            break;
            case '👥 Рефералы':
                $this->adminReferralService->handle($chatId, $messageId);
                break;
            case 'Выйти из режима администратора':
                $this->activateSessionService->deactivate($chatId, $messageId);
                break;
            default:
                $this->unknownCommandService->handle($dto);
        }
    }
}
