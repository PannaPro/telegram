<?php

namespace App\Service\Telegram\Admin\Command\Context;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\Service\AdminReferralSearchService;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;

class ReferralSearchCommandHandler
{
    public function __construct(
        private AdminReferralSearchService $searchService,
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

        try {
            $result = $this->parseInput($text);
            // TODO изменить на кастомную ContextException -> Domain
        } catch (\DomainException $e) {
            $this->searchService->errorInputMessage($chatId, $e->getMessage(), $messageId);
            return;
        }

        switch ($result['type']) {
            case 'integer':
                $this->searchService->setParticipantCount($chatId, $context, $result['value']);
                break;

            case 'day_period':
            case 'date_range_period':
                $this->searchService->setCustomSearchDate($chatId, $context, $result);
                break;
        }
    }

    private function handleCallback(CallbackQueryTelegramPayload $payload, ReferralSearchContext $context): void
    {
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $data = $payload->getCallbackData();

        switch ($data) {
            case 'back_to_admin_menu':
                $this->searchService->backToAdminMenuAction($chatId, $callbackId);
                break;
            case 'back_to_referral_menu':
                $this->searchService->backToReferralMenu($chatId, $callbackId);
                break;
            case 'all_period':
            case 'current_day_period':
            case 'week_period':
                $this->searchService->setSearchDate($chatId, $callbackId, $data, $context);
                break;
            case 'back_to_chose_search_date':
                $this->searchService->backToSearchDate($chatId, $callbackId, $context);
                break;
            case 'search_referral':
                $this->searchService->executeSearchAction($chatId, $callbackId, $context);
                break;
        }
    }

    private function parseInput(string $text): array
    {
        $text = trim($text);

        $text = preg_replace('/[^0-9\-\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        if (ctype_digit($text)) {
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
        $dt = \DateTime::createFromFormat('d-m-Y', $date);

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
        $fromDt = \DateTime::createFromFormat('d-m-Y', $from);
        $toDt   = \DateTime::createFromFormat('d-m-Y', $to);

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
