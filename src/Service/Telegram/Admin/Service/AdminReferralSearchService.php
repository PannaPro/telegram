<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminReferralSearchMessage;
use App\Service\Telegram\Message\AdminTopReferralMessage;
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
        private AdminReferralSearchMessage $referralSearchMessage,
        private AdminReferralService $adminReferralService,
    ) {
    }

    public function makeTopReferralAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);
        $this->setContext($chatId);

        $messageId = $this->referralSearchMessage->sendParticipantStatusMessage($chatId);

        $this->cache->deleteMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->setExMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, TelegramCacheKey::TTL_1_HOUR, $messageId);
        $this->cache->cleanup(TelegramCacheKey::STEP, $chatId);
        $this->cache->cleanup(TelegramCacheKey::START_MENU, $chatId);
    }

    public function backToReferralMenuAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
        $this->adminReferralService->makeAction($chatId);

        $this->cache->cleanup(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->cleanup('error_message', $chatId);
    }

    public function participantStatusAction(int $chatId, int $callbackId, ReferralSearchContext $context, string $data): void
    {
        $this->answerCallbackQuery($callbackId);

        $context->setSearchType($data);
        $context->setTextType('Статус участник');
        $context->setBlockContext(false);
        $this->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $textHeader = $context->getTextType();
        $this->referralSearchMessage->editDataMessage($chatId, $messageId, $textHeader);
    }

    public function searchDateAction(int $chatId, int $callbackId, ReferralSearchContext $context, string $data): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setDateType($data);
        $context->setSearchDate((new \DateTime())->format('Y-m-d'));
        $context->setDateText($this->buildDateText($data));
        $this->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $textHeader = $context->getTextType() . ' ' . $context->getDateText();
        $this->referralSearchMessage->editCountMessage($chatId, $messageId, $textHeader);
    }

    public function participantCountAction(int $chatId, int $currentMessage, ReferralSearchContext $context, int $count): void
    {
        $context->setCount($count);
        $context->setBlockContext(true);
        $this->updateContext($chatId, $context);

        $contextMessage = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $textHeader = $context->getTextType() . " " . $context->getDateText() . " не менее $count рефералов";

        $this->referralSearchMessage->editReferralSearchMessage($chatId, $contextMessage, $textHeader);
        $this->bot->deleteMessage($chatId, $currentMessage);
        $this->cache->deleteMessage('error_message', $chatId);
    }

    public function customSearchDateAction(int $chatId, int $currentMessage, ReferralSearchContext $context, array $result): void
    {
        $key = $result['type'];
        if ($key === 'date_range_period') {
            $from = $result['from'];
            $to = $result['to'];
            $context->setRangeStart($result['from']);
            $context->setRangeEnd($result['to']);
            $dateText = "поиск с $from по $to";
        } else {
            $context->setSearchDate($result['value']);
            $dateText = "поиск за " . $result['value'] . " число";
        }

        $context->setDateText($dateText);
        $context->setDateType($key);

        $this->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $textHeader = $context->getTextType() . " " . $context->getDateText();

        $this->referralSearchMessage->editCountMessage($chatId, $messageId, $textHeader);
        $this->bot->deleteMessage($chatId, $currentMessage);
        $this->cache->deleteMessage('error_message', $chatId);
    }

    public function backToSearchDate(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $context->setDateType(null);
        $context->setSearchDate(null);
        $context->setDateText(null);
        $context->setBlockContext(false);
        $this->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $textHeader = $context->getTextType();
        $this->referralSearchMessage->editDataMessage($chatId, $messageId, $textHeader);
    }

    public function executeSearchAction(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->referralSearchMessage->editSearchMessage($chatId, $messageId);

        // TODO очередь
        $this->adminReferralService->search($context, $messageId);
    }

    public function errorInputMessage(int $chatId, string $errorText, int $currentMessage): void
    {
        $messageId = $this->topReferralMessage->sendErrorMessage($chatId, $errorText);

        $this->cache->saveAndCleanup('error_message', $chatId, $currentMessage, $messageId);
    }

    public function backToAdminMenuAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId);
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
    }

    public function backToParticipantAction(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setSearchType(null);
        $context->setTextType(null);
        $context->setDateType(null);
        $context->setDateText(null);
        $context->setSearchDate(null);
        $context->setRangeEnd(null);
        $context->setRangeEnd(null);
        $context->setCount(null);
        $context->setBlockContext(true);

        $this->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->referralSearchMessage->editParticipantMessage($chatId, $messageId);
    }

    private function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }

    private function updateContext(int $chatId, ReferralSearchContext $context): void
    {
        $this->contextStorage->updateContext($chatId, $context);
    }

    private function setContext(int $chatId): void
    {
        $context = new ReferralSearchContext($chatId);

        $this->contextStorage->setContext($chatId, $context);
    }

    private function unsetContext($chatId): void
    {
        $this->contextStorage->unsetContext($chatId);
    }

    private function buildDateText(string $date): string
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
}
