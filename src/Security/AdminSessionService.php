<?php

namespace App\Security;

use App\Repository\AdminRepository;
use App\Service\Telegram\Admin\Service\WaitingPasswordService;
use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\WaitingPasswordContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\TelegramMessageCache;

class AdminSessionService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private SecurityTelegramUserService $security,
        private ContextStorage $contextStorage,
        private WaitingPasswordService $waitingPasswordService,
        private AdminRepository $adminRepository,
    ) {
    }

    public function checkPassword(int $chatId, string $text): bool
    {
        $hash = hash('sha256', $chatId . ':' . $text);

        $exist = $this->adminRepository->findOneBy(['chatId' => $chatId, 'password' => $hash]);

        return (bool)$exist;
    }

    public function answerCallbackQuery(int $callbackId): void
    {
        $this->waitingPasswordService->answerCallbackQuery($callbackId);
    }

    public function isAdmin(): bool
    {
        $user = $this->security->fetchCurrentUser();

        return $user->isAdmin();
    }

    public function activateWaitingPassword(int $chatId, int $currentMessage): void
    {
        $this->contextStorage->setContext($chatId, new WaitingPasswordContext($chatId, true), TelegramCacheKey::TTL_5_MINUTES);

        $this->waitingPasswordService->waitingPassword($chatId, $currentMessage);
    }

    public function isWaitingPassword(int $chatId): bool
    {
        $context = $this->contextStorage->getContext($chatId);
        if ($context instanceof WaitingPasswordContext) {
            return true;
        }

        return false;
    }

    public function deactivateWaitingPassword(int $chatId): void
    {
        $this->contextStorage->unsetContext($chatId);
    }

    public function deactivateAdminSession(int $chatId): void
    {
        $this->cache->deleteMessage("admin_session", $chatId);
    }

    public function isAdminSessionActive(): bool
    {
        $user = $this->security->fetchCurrentUser();
        if (!$user->isAdmin()) {
            return false;
        }

        $chatId = $user->getChatId();
        $isWaitingPassword = $this->isWaitingPassword($chatId);
        $isAdminSession = $this->isActiveAdminSession($chatId);

        return $isWaitingPassword || $isAdminSession;
    }

    public function activateAdminSession(int $chatId): void
    {
        $this->cache->setExMessage("admin_session", $chatId, TelegramCacheKey::TTL_48_HOURS, $chatId);
    }

    private function isActiveAdminSession(int $chatId): bool
    {
        return $this->cache->getMessage("admin_session", $chatId) === "$chatId";
    }
}
