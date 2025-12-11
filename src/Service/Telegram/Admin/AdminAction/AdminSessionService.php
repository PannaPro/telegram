<?php

namespace App\Service\Telegram\Admin\AdminAction;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\Telegram\User\Action\Menu\MenuService;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class AdminSessionService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private TelegramMessageCache $cache,
        private TelegramBotService $telegramBotService,
        private MenuService $menuService,
        private AdminMenuService $adminMenuService,
    ) {
    }


    /**
     * При получении команды, отправляем сообщение и ожидаем ввод
     */
    public function handle(int $currentMessage): bool
    {
        $user = $this->security->fetchCurrentUser();
        if ($user->isAdmin() === false) {
            return false;
        }
        $chatId = $user->getChatId();

        /** TODO создать сервис приглашения в закрытый канал, генерацию ссылок одноразовых
         *   в админке, кнопка рефералы, всего участников в боте (не боты и каналы)
         *   показть топов -реферал -реферал+ЦД -
         *      реферал - искать в базе тех кто participant >
         *      реферал+ЦД - искать в базе тех кто participant - hasPaid >
         *   выберите даты
         *      за все время, Даты 01-12-2025 30-12-2025, День 22-12-2025
         *      кол-во рефералов - рефералы любое / цифра
         *   response:
         *      возвращать Топ-5 и всего кол-во
         *      кнопка, выгрузить в файл
         *      наградить участников
         *      назад - Показать топов - реферал или реферал+ЦД
         *
         *    если не найдено, показать шаг Показать топов - реферал или реферал+ЦД
         *
         *   Выгрузить Excel - заголовок, формируется из параметров запроса, юезрнейм телеграм айди кол-во по убыванию
         *
         *  Доп научиться создать приватные одноразовые ссылки на закрытый чат
         *  Починить чат айди в редис апдейте гард.
         */

        $text = <<<MARKDOWN
            Активация режима администратора
            Введите пароль:
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "Закрыть", 'callback_data' => 'close_password_menu'],
            ],
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->setWaitingPassword($chatId);
        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $message->getMessageId());
        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());

        return true;
    }

    public function incorrectPassword(int $currentMessage): bool
    {
        $user = $this->security->fetchCurrentUser();
        if ($user->isAdmin() === false) {
            return false;
        }
        $chatId = $user->getChatId();

        $text = <<<MARKDOWN
            Не правильный пароль.
            Введите пароль еще раз:
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "Закрыть", 'callback_data' => 'close_password_menu'],
            ],
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->setWaitingPassword($chatId);
        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());

        return true;
    }

    public function deactivate(int $chatId, int $messageId): void
    {
        $this->cache->delete("admin_session", $chatId);
        $this->cache->delete("admin_password_wait", $chatId);

        $this->menuService->sendStartMenu($chatId, $messageId);
    }

    public function isWaitingPassword(int $chatId): bool
    {
        return $this->cache->get("admin_password_wait", $chatId) == true;
    }

    public function setWaitingPassword(int $chatId): void
    {
        $this->cache->setEx("admin_password_wait", $chatId, TelegramCacheKey::TTL_5_MINUTES, 1);
    }

    public function isActiveAdminSession(int $chatId): bool
    {
        return $this->cache->get("admin_session", $chatId) === "$chatId";
    }

    public function handleAdminPassword(int $chatId, string $password): void
    {
        if ($password === 'password') {
            $this->deactivateWaitingPassword($chatId);
            $this->activateAdminSession($chatId);
            $this->adminMenuService->handle($chatId);
        } else {
            $this->incorrectPassword($chatId);
        }
    }

    public function activateAdminSession(int $chatId): void
    {
        $this->cache->setEx("admin_session", $chatId, TelegramCacheKey::TTL_1_HOUR, $chatId);
    }

    private function deactivateWaitingPassword(int $chatId): void
    {
        $this->cache->delete("admin_password_wait", $chatId);
    }
}
