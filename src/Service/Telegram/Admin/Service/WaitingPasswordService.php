<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\WaitingPasswordContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\IncorrectPasswordMessage;
use App\Service\Telegram\Message\WaitingPasswordMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\Telegram\User\Service\StartService;
use App\Service\TelegramBotService;

class WaitingPasswordService
{
    public function __construct(
        private TelegramBotService $bot,
        private TelegramMessageCache $cache,
        private ContextStorage $contextStorage,
        private WaitingPasswordMessage $waitingPasswordMessage,
        private IncorrectPasswordMessage $incorrectPasswordMessage,
    ) {
    }

    public function waitingPassword(int $chatId, int $currentMessage): void
    {
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
        $messageId = $this->waitingPasswordMessage->sendMessage($chatId);

        $this->contextStorage->setContext($chatId, new WaitingPasswordContext($chatId, true));
        $this->cache->cleanup(TelegramCacheKey::START_MENU, $chatId);
        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }

    public function incorrectPassword(int $chatId, int $currentMessage): void
    {
        $messageId = $this->incorrectPasswordMessage->sendMessage($chatId);

        $this->cache->saveAndClean(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }

    public function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }
}
