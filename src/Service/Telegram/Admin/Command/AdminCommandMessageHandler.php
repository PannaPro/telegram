<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\MessageTelegramPayload;
use App\Security\AdminSessionService;
use App\Service\Telegram\Admin\Service\AdminGameService;
use App\Service\Telegram\Admin\Service\AdminMenuService;
use App\Service\Telegram\Admin\Service\AdminParticipantService;
use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Common\UnknownCommandService;
use App\Service\Telegram\Object\CurrentTelegramMessage;
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

        $currentMessage = new CurrentTelegramMessage($messageId);

        switch ($text) {
            case '/start':
            case '⬅ Вернуться в меню':
                $this->adminMenuService->handle($chatId, $currentMessage);
                break;
            case '👥 Рефералы':
                $this->adminReferralService->makeAction($chatId, $currentMessage);
                break;
//            case '1️⃣ Участники':
//                $this->adminParticipantService->makeAction($chatId, $messageId);
//                break;
            case '🎮 События':
                $this->adminGameService->makeAction($chatId, $currentMessage);
                break;
            case '🎮 Создать событие':
                $this->adminGameService->createEvent($chatId, $currentMessage);
                break;
            case '✏️ Управлять событием':
                $this->adminGameService->manageEvent($chatId, $currentMessage);
                break;
            case 'Выйти из режима администратора':
                $this->adminSession->deactivateAdminSession($chatId);
                $this->startService->makeAction($currentMessage);
                break;
            default:
                $this->unknownCommandService->makeAction($payload);
        }
    }
}
