<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\MessageTelegramPayload;
use App\Security\AdminSessionService;
use App\Service\Telegram\Admin\Service\AdminMenuService;
use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Common\UnknownCommandService;
use App\Service\Telegram\User\Service\StartService;

class AdminCommandMessageHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private AdminSessionService $adminSession,
        private StartService $startService,
        private AdminMenuService $adminMenuService,
        private AdminReferralService $adminReferralService,
    ) {
    }

    public function handleCommand(MessageTelegramPayload $payload): void
    {
        $text = $payload->getText();
        $messageId = $payload->getMessageId();
        $chatId = $payload->getChatId();

        switch ($text) {
            case '/start':
            case 'Вернуться в меню':
                $this->adminMenuService->handle($chatId, $messageId);
                break;
            case '👥 Рефералы':
                $this->adminReferralService->makeAction($chatId, $messageId);
                break;
            case 'Выйти из режима администратора':
                $this->adminSession->deactivateAdminSession($chatId);
                $this->startService->makeAction();
                break;
            default:
                $this->unknownCommandService->makeAction($payload);
        }
    }
}
