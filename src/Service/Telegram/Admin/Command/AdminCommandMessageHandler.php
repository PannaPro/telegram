<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\MessageTelegramPayload;
use App\Security\AdminSessionService;
use App\Service\Telegram\Admin\Service\AdminGameService;
use App\Service\Telegram\Admin\Service\AdminMenuService;
use App\Service\Telegram\Admin\Service\AdminParticipantService;
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
        private AdminParticipantService $adminParticipantService,
        private AdminGameService $adminGameService,
    ) {
    }

    public function handleCommand(MessageTelegramPayload $payload): void
    {
        $text = $payload->getText();
        $messageId = $payload->getMessageId();
        $chatId = $payload->getChatId();

        switch ($text) {
            case '/start':
            case '⬅ Вернуться в меню':
                $this->adminMenuService->handle($chatId, $messageId);
                break;
            case '👥 Рефералы':
                $this->adminReferralService->makeAction($chatId, $messageId);
                break;
//            case '1️⃣ Участники':
//                $this->adminParticipantService->makeAction($chatId, $messageId);
//                break;
            case '🎮 События':
                $this->adminGameService->makeAction($chatId, $messageId);
                break;
            case '🎮 Создать событие':
                $this->adminGameService->createEvent($chatId, $messageId);
                break;
            case 'Выйти из режима администратора':
                $this->adminSession->deactivateAdminSession($chatId);
                $this->startService->makeAction($messageId);
                break;
            default:
                $this->unknownCommandService->makeAction($payload);
        }
    }
}
