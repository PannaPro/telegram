<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\Message\AdminTopReferralMessage;
use App\Service\Telegram\Message\ReferralSearchCountMessage;
use App\Service\Telegram\Message\ReferralSearchDateMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;

class AdminReferralSearchService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private TelegramBotService $bot,
        private ContextStorage $contextStorage,
        private AdminMenuService $adminMenuService,
        private AdminTopReferralMessage $topReferralMessage,
        private ReferralSearchDateMessage $dateMessage,
        private ReferralSearchCountMessage $countMessage,
    ) {
    }

    public function errorInputMessage(int $chatId, string $errorText, int $currentMessage): void
    {
        $messageId = $this->topReferralMessage->sendErrorMessage($chatId, $errorText);

        $this->cache->saveAndCleanup('errorMessage', $chatId, $currentMessage, $messageId);
    }

    private function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }

    public function makeParticipantReferralAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);

        $text = '*Поиск рефералов со статусов участник*';
        $this->setContext($chatId, 'participant_referral', $text);
        $messageId = $this->dateMessage->sendMessage($chatId, $text);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $messageId);
    }

    private function setContext(int $chatId, string $searchType, string $text): void
    {
        $context = new ReferralSearchContext($chatId, $searchType, $text);

        $this->contextStorage->setContext($chatId, $context);
    }

    private function unsetContext($chatId): void
    {
        $this->contextStorage->unsetContext($chatId);
    }

    public function backToAdminMenuAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
    }

    public function backToReferralMenu(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
        $messageId = $this->topReferralMessage->sendMessage($chatId);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
        $this->cache->delete('errorMessage', $chatId);
    }

    public function setSearchDate(int $chatId, int $callbackId, string $date, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $dateMessage = $this->createDateText($date);
        $context->setSearchDate($date);
        $context->setText($context->getText() . " $dateMessage");
        $this->contextStorage->updateContext($chatId, $context);
        $messageId = $this->countMessage->sendMessage($chatId, $dateMessage);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
        $this->cache->delete('errorMessage', $chatId);
    }

    public function setCustomSearchDate(int $chatId, ReferralSearchContext $context, array $date): void
    {
        $key = $date['type'];
        if ($key === 'date_range_period') {
            $from = $date['from'];
            $to = $date['to'];
            $context->setDateType($key);
            $context->setRangeStart($date['from']);
            $context->setRangeEnd($date['to']);
            $dateText = "поиск с $from по $to";
        } else {
            $context->setDateType($key);
            $context->setSearchDate($date['value']);
            $dateText = "поиск за " . $date['value'];
        }

        $newText = $context->getText() . " $dateText";
        $context->setText($newText);

        $this->contextStorage->updateContext($chatId, $context);
        $messageId = $this->countMessage->sendMessage($chatId, $dateText);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
        $this->cache->delete('errorMessage', $chatId);
    }

    public function setParticipantCount(int $chatId, ReferralSearchContext $context, int $count): void
    {
        $newText = $context->getText() . " кол-во рефералов $count";
        $context->setText($newText);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->topReferralMessage->sendApproveMessage($chatId, $newText);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
        $this->cache->delete('errorMessage', $chatId);
    }

    public function backToSearchDate(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $text = $context->getSearchType() === 'participant_referral' ? '*Поиск рефералов со статусов участник*' : TelegramDefaultValue::UNKNOWN;
        $context->setText($text);
        $this->contextStorage->updateContext($chatId, $context);
        $messageId = $this->dateMessage->sendMessage($chatId, $text);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function executeSearchAction(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $this->topReferralMessage->editMessage($context);
    }

    private function createDateText(string $date): string
    {
        if ($date === 'all_period') {
            return 'за все время';
        } elseif ($date === 'week_period') {
            return 'за неделю';
        } elseif ($date === 'current_day_period') {
            return 'за сегодняшний день';
        }

        return $date;
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
     */
//    public function prepareToSearch(int $chatId, string $searchType, int $currentMessage = 0): void
//    {
//        $text = <<<MARKDOWN
//        *Поиск по Участникам (статус participant)*
//
//        Выбери дату для поиска.
//
//        Для альтернативного поиска введите команду в формате:
//        *День 01-12-2025*
//        *Даты 01-12-2025 31-12-2025*
//        MARKDOWN;
//
//        $keyboard = new InlineKeyboardMarkup([
//            [
//                ['text' => 'За все время', 'callback_data' => 'allPeriod'],
//                ['text' => 'Сегодня', 'callback_data' => 'dayPeriod'],
//                ['text' => 'За неделю', 'callback_data' => 'weekPeriod'],
//            ],
//            [
//                ['text' => 'Назад', 'callback_data' => 'back_to_referral_menu'],
//                ['text' => 'Главное меню', 'callback_data' => 'back_to_admin_menu'],
//            ]
//        ]);
//
//        $message = $this->telegramBotService->sendMessage(
//            $chatId,
//            $text,
//            TelegramParseMode::MARKDOWN,
//            false,
//            null,
//            $keyboard
//        );
//
////        $this->referralSearchStorage->setReferralSearchWaiting($chatId);
////        $this->referralSearchStorage->setReferralSearchContext($chatId, $searchType);
//        $this->cache->saveAndCleanup(TelegramCacheKey::START_MENU, $chatId, $message->getMessageId());
//        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
//    }
//
//    public function prepareDataToSearch(int $chatId, string $date, int $currentMessage = 0): void
//    {
////        $context = $this->referralSearchStorage->loadReferralSearchContext($chatId);
//        $context->setSearchDate($date);
//
//        $dateMessage = 'за все время';
//
//        $text = <<<MARKDOWN
//        *Поиск по Участникам (статус participant) $dateMessage*
//
//        Введите кол-во участников для поиска:
//        MARKDOWN;
//
//        $keyboard = new InlineKeyboardMarkup([
//            [
//                ['text' => 'Назад', 'callback_data' => 'back_to_referral_menu'],
//            ]
//        ]);
//
//        $message = $this->telegramBotService->sendMessage(
//            $chatId,
//            $text,
//            TelegramParseMode::MARKDOWN,
//            false,
//            null,
//            $keyboard
//        );
//
////        $this->referralSearchStorage->saveReferralSearchContext($chatId, $context);
//
//        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
//    }
}
