<?php

namespace App\Service\Telegram\Admin\AdminAction\ReferralSearch;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ReferralSearchService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private TelegramBotService $telegramBotService,
        private ReferralSearchStorage $referralSearchStorage,
    ) {
    }

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
    public function prepareToSearch(int $chatId, string $searchType, int $currentMessage = 0): void
    {
        $text = <<<MARKDOWN
        *Поиск по Участникам (статус participant)*

        Выбери дату для поиска.

        Для альтернативного поиска введите команду в формате:
        *День 01-12-2025*
        *Даты 01-12-2025 31-12-2025*
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'За все время', 'callback_data' => 'allPeriod'],
                ['text' => 'Сегодня', 'callback_data' => 'dayPeriod'],
                ['text' => 'За неделю', 'callback_data' => 'weekPeriod'],
            ],
            [
                ['text' => 'Назад', 'callback_data' => 'back_to_referral_menu'],
                ['text' => 'Главное меню', 'callback_data' => 'back_to_admin_menu'],
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->referralSearchStorage->setReferralSearchWaiting($chatId);
        $this->referralSearchStorage->setReferralSearchContext($chatId, $searchType);
        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $message->getMessageId());
        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }

    public function prepareDataToSearch(int $chatId, string $date, int $currentMessage = 0): void
    {
        $context = $this->referralSearchStorage->loadReferralSearchContext($chatId);
        $context->setSearchDate($date);

        $dateMessage = 'за все время';

        $text = <<<MARKDOWN
        *Поиск по Участникам (статус participant) $dateMessage*

        Введите кол-во участников для поиска:
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Назад', 'callback_data' => 'back_to_referral_menu'],
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->referralSearchStorage->saveReferralSearchContext($chatId, $context);

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }
}
