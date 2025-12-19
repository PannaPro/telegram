<?php

namespace App\Service\Telegram\Admin\Service;

use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\Telegram\Message\AdminReferralSearchMessage;
use App\Service\Telegram\Message\AdminTopReferralMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use DateTime;

readonly class AdminReferralSearchService
{
    use AnswerCallbackQueryTrait;

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
        $this->answerCallbackQuery($callbackId, 'контекст: поиск рефералов');
        $this->setContext($chatId);

        $messageId = $this->referralSearchMessage->sendParticipantStatusMessage($chatId);

        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->setEx(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, TelegramCacheKey::TTL_1_HOUR, $messageId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::START_MENU, $chatId);
    }

    public function backToReferralMenuAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId, 'контекст закрыт');
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
        $this->adminReferralService->makeAction($chatId);

        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function participantStatusAction(int $chatId, int $callbackId, ReferralSearchContext $context, string $data): void
    {
        $this->answerCallbackQuery($callbackId, 'статус выбран');

        $context->setSearchType($data);
        $context->setTextType($this->buildStatusText($data));
        $context->setBlockContext(false);
        $this->updateContext($chatId, $context);

        $messageId = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $textHeader = $context->getTextType();
        $this->referralSearchMessage->editDataMessage($chatId, $messageId, $textHeader);

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function searchDateAction(int $chatId, int $callbackId, ReferralSearchContext $context, string $data): void
    {
        $this->answerCallbackQuery($callbackId, 'дата выбрана');

        $this->manageSearchDateParameters($chatId, $context, $data);

        $messageId = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $textHeader = $context->getTextType() . ' ' . $context->getDateText();
        $this->referralSearchMessage->editCountMessage($chatId, $messageId, $textHeader);

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function participantCountAction(int $chatId, int $currentMessage, ReferralSearchContext $context, int $count): void
    {
        $context->setCount($count);
        $context->setBlockContext(true);
        $this->updateContext($chatId, $context);

        $contextMessage = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $textHeader = $context->getTextType() . " " . $context->getDateText() . " не менее $count рефералов";
        $context->setTextType($textHeader);
        $this->updateContext($chatId, $context);

        $this->referralSearchMessage->editReferralSearchMessage($chatId, $contextMessage, $textHeader);
        $this->cache->delete($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::ERROR_MESSAGE, $chatId);
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

        $contextMessage = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $textHeader = $context->getTextType() . " " . $context->getDateText();

        $this->referralSearchMessage->editCountMessage($chatId, $contextMessage, $textHeader);
        $this->cache->deleteMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::ERROR_MESSAGE, $chatId);
    }

    public function backToSearchDate(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $context->setDateType(null);
        $context->setSearchDate(null);
        $context->setRangeStart(null);
        $context->setRangeEnd(null);
        $context->setDateText(null);
        $context->setBlockContext(false);
        $this->updateContext($chatId, $context);

        $contextMessage = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->referralSearchMessage->editDataMessage($chatId, $contextMessage, $context->getTextType());

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function executeSearchAction(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId, 'поиск подтвержден');

        $contextMessage = $this->cache->get(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->referralSearchMessage->editSearchMessage($chatId, $contextMessage);

        // TODO очередь
        $this->adminReferralService->search($context);
    }

    public function errorInputMessage(int $chatId, string $errorText, int $currentMessage): void
    {
        $messageId = $this->topReferralMessage->sendErrorMessage($chatId, $errorText);

        $this->cache->deleteMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function backToAdminMenuAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId, 'контекст закрыт');
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function backToAdminMenuHandler(int $chatId, int $messageId): void
    {
        $this->unsetContext($chatId);

        $this->adminMenuService->handle($chatId, $messageId);
        $this->cache->delete(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
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
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
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

    private function buildStatusText(string $type): string
    {
        if ($type === 'participant_referral') {
            return 'Статус участник';
        }

        return 'Статус участник и есть пополнение';
    }

    private function manageSearchDateParameters(int $chatId, ReferralSearchContext $context, string $dateType): void
    {
        if ($dateType === 'current_day_period') {
            $date = new DateTime();
            $text = "за " . $date->format('d-m-Y');

            $context->setSearchDate($date->format('Y-m-d'));
            $context->setRangeStart(null);
            $context->setRangeEnd(null);
        } elseif ($dateType === 'week_period'){
            $today = new DateTime();
            $sevenDaysAgo = (clone $today)->modify('-7 days');
            $text = "с " . $sevenDaysAgo->format('d-m-Y') . " по " . $today->format('d-m-Y');

            $context->setSearchDate(null);
            $context->setRangeStart($sevenDaysAgo->format('Y-m-d'));
            $context->setRangeEnd($today->format('Y-m-d'));
        } elseif ($dateType === 'yesterday_period') {
            $date = (new DateTime())->modify('-1 day');
            $text = "за " . $date->format('d-m-Y');

            $context->setSearchDate($date->format('Y-m-d'));
            $context->setRangeStart(null);
            $context->setRangeEnd(null);
        } else {
            $text = 'за все время';

            $context->setSearchDate(null);
            $context->setRangeStart(null);
            $context->setRangeEnd(null);
        }

        $context->setDateType($dateType);
        $context->setDateText($text);

        $this->updateContext($chatId, $context);
    }
}
