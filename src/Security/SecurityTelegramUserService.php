<?php

namespace App\Security;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;

class SecurityTelegramUserService
{
    public function __construct(
        private LoadTelegramUserService $telegramUserService,
    ) {
    }

    private ?TelegramUser $currentUser = null;

    public function setCurrentTelegramUser(AbstractPayload $payload): void
    {
        $user = $this->telegramUserService->load($payload);

        $this->currentUser = $user;
    }

    public function fetchCurrentUser(): TelegramUser
    {
        return $this->currentUser;
    }

    public function clearCurrentUser(): void
    {
        $this->currentUser = null;
    }
}
