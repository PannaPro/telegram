<?php

namespace App\Security;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;
use App\Service\ExceptionHandle\NotFoundException;
use App\Service\Telegram\TelegramUserService;

class SecurityTelegramUserService
{
    public function __construct(
        private TelegramUserService $telegramUserService,
    ) {
    }

    private ?TelegramUser $currentUser = null;

    public function setCurrentTelegramUser(AbstractPayload $payload): void
    {
        $user = $this->telegramUserService->loadTelegramUser($payload);

        $this->currentUser = $user;
    }

    public function fetchCurrentUser(): TelegramUser
    {
        if (!$this->currentUser) {
            throw NotFoundException::userNotFound();
        }

        return $this->currentUser;
    }
}
