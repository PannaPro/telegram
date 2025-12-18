<?php

namespace App\Service\Telegram\Admin\Command\Context;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\Service\AdminReferralSearchService;
use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Common\CommonActionService;
use App\Service\Telegram\Common\UnknownCommandService;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use DateTime;

class ReferralSearchCommandHandler
{
    public function __construct(
        private AdminReferralSearchService $searchService,
        private UnknownCommandService $unknownCommandService,
        private AdminReferralService $adminReferralService,
        private CommonActionService $commonActionService,
    ) {
    }

    public function handleCommand(AbstractPayload $payload, ReferralSearchContext $context): void
    {
        switch (true) {
            case $payload instanceof MessageTelegramPayload:
                $this->handleMessage($payload, $context);
                break;
            case $payload instanceof CallbackQueryTelegramPayload:
                $this->handleCallback($payload, $context);
                break;
        }
    }

    private function handleMessage(MessageTelegramPayload $payload, ReferralSearchContext $context): void
    {
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();
        $text = $payload->getText();

        if ($text === '/start') {
            $this->searchService->backToAdminMenuHandler($chatId, $messageId);
            return;
        }

        if ($context->isBlockContext()) {
            $this->unknownCommandService->makeAction($payload);
            return;
        }

        try {
            $result = $this->parseInput($text, $context->getDateType());
            // TODO изменить на кастомную ContextException -> Domain
        } catch (\DomainException $e) {
            $this->searchService->errorInputMessage($chatId, $e->getMessage(), $messageId);
            return;
        }

        switch ($result['type']) {
            case 'integer':
                $this->searchService->participantCountAction($chatId, $messageId, $context, $result['value']);
                break;

            case 'day_period':
            case 'date_range_period':
                $this->searchService->customSearchDateAction($chatId, $messageId, $context, $result);
                break;
        }
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload, ReferralSearchContext $context): void
    {
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();
        $messageId = $payload->getMessageId();

        switch ($data) {
            case 'back_to_referral_menu':
                $this->searchService->backToReferralMenuAction($chatId, $callbackId);
                break;
            case 'back_to_admin_menu':
                $this->searchService->backToAdminMenuAction($chatId, $callbackId);
                break;
            case 'participant_cd_referral':
            case 'participant_referral':
                $this->searchService->participantStatusAction($chatId, $callbackId, $context, $data);
                break;
            case 'back_to_referral_status':
                $this->searchService->backToParticipantAction($chatId, $callbackId, $context);
                break;
            case 'all_period':
            case 'yesterday_period':
            case 'current_day_period':
            case 'week_period':
                $this->searchService->searchDateAction($chatId, $callbackId, $context, $data);
                break;
            case 'back_to_search_date':
                $this->searchService->backToSearchDate($chatId, $callbackId, $context);
                break;
            case 'search_referral':
                $this->searchService->executeSearchAction($chatId, $callbackId, $context);
                break;
            case 'download_search_result':
                $this->adminReferralService->downloadResult($chatId, $callbackId, $context);
                break;
            case 'close_pinned_message':
                $this->commonActionService->deletePinnedMessage($chatId, $callbackId, $messageId);
                break;
            case 'search_top_referral':
                $this->searchService->makeTopReferralAction($chatId, $callbackId);
        }
    }

    private function parseInput(string $text, ?string $hasDateType): array
    {
        $text = trim($text);

        $text = preg_replace('/[^0-9\-\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        if (ctype_digit($text)) {
            if ($hasDateType === null) {
                throw new \DomainException('Некорректный формат ввода');
            }

            $count = (int)$text;

            if ($count > 1000) {
                throw new \DomainException('Указано слишком большое число');
            }

            return [
                'type'  => 'integer',
                'value' => $count,
            ];
        }

        if (preg_match('/^(\d{2}-\d{2}-\d{4}) (\d{2}-\d{2}-\d{4})$/', $text, $m)) {
            return $this->parseDateRange($m[1], $m[2]);
        }

        if (preg_match('/^(\d{2}-\d{2}-\d{4})(\d{2}-\d{2}-\d{4})$/', $text, $m)) {
            return $this->parseDateRange($m[1], $m[2]);
        }

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $text)) {
            return $this->parseSingleDate($text);
        }

        throw new \DomainException('Некорректный формат ввода');
    }

    private function parseSingleDate(string $date): array
    {
        $dt = DateTime::createFromFormat('d-m-Y', $date);

        if (!$dt || $dt->format('d-m-Y') !== $date) {
            throw new \DomainException('Некорректная дата');
        }

        return [
            'type'  => 'day_period',
            'value' => $date,
        ];
    }

    private function parseDateRange(string $from, string $to): array
    {
        $fromDt = DateTime::createFromFormat('d-m-Y', $from);
        $toDt   = DateTime::createFromFormat('d-m-Y', $to);

        if (
            !$fromDt || $fromDt->format('d-m-Y') !== $from ||
            !$toDt   || $toDt->format('d-m-Y') !== $to
        ) {
            throw new \DomainException('Некорректный диапазон дат');
        }

        if ($fromDt > $toDt) {
            throw new \DomainException('Дата начала больше даты окончания');
        }

        return [
            'type' => 'date_range_period',
            'from' => $from,
            'to'   => $to,
        ];
    }
}
