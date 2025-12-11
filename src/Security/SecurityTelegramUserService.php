<?php

namespace App\Security;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;
use App\Service\Telegram\TelegramUserService;

class SecurityTelegramUserService
{
    public function __construct(
        private TelegramUserService $telegramUserService,
    ) {
    }

    private ?TelegramUser $currentUser = null;

    public function setCurrentTelegramUser(AbstractPayload $payload): TelegramUser
    {
        $user = $this->telegramUserService->loadTelegramUser($payload);

        $this->currentUser = $user;

        return $user;
    }

    public function fetchCurrentUser(): TelegramUser
    {
        return $this->currentUser;
    }
}
