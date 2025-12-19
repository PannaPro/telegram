<?php

namespace App\Service\Telegram\Admin\Command\Context;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Security\AdminSessionService;
use App\Service\Telegram\Admin\Service\AdminMenuService;
use App\Service\Telegram\Admin\Service\WaitingPasswordService;
use App\Service\Telegram\User\Service\StartService;

class WaitingPasswordCommandHandler
{
    public function __construct(
        private WaitingPasswordService $waitingPasswordService,
        private AdminMenuService $adminMenuService,
        private AdminSessionService $adminSession,
        private StartService $startService,
    ) {
    }

    public function handleCommand(AbstractPayload $payload): void
    {
        switch (true) {
            case $payload instanceof MessageTelegramPayload:
                $this->handleMessage($payload);
                break;
            case $payload instanceof CallbackQueryTelegramPayload:
                $this->handleCallback($payload);
                break;
        }
    }

    private function handleMessage(MessageTelegramPayload $payload): void
    {
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();
        $text = $payload->getText();

        if ($this->adminSession->checkPassword($chatId, $text)) {
            $this->adminSession->deactivateWaitingPassword($chatId);
            $this->adminSession->activateAdminSession($chatId);
            $this->adminMenuService->handle($chatId, $messageId);
            return;
        }

        $this->waitingPasswordService->incorrectPassword($chatId, $messageId);
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload): void
    {
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();

        if ($data === 'close_password_menu') {
            $this->adminSession->answerCallbackQuery($callbackId);
            $this->adminSession->deactivateWaitingPassword($chatId);
            $this->startService->makeAction();
        }
    }
}
