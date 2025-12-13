<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class ReferralSearchContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public string $searchType,
        public ?string $searchDate = TelegramDefaultValue::UNKNOWN,
        public ?int $searchReferralCount = TelegramDefaultValue::ZERO,
        public ?string $rangeStart = TelegramDefaultValue::UNKNOWN,
        public ?string $rangeEnd = TelegramDefaultValue::UNKNOWN,
    ) {
    }

    public function toArray(): array
    {
        return [
            'chatId' => $this->chatId,
            'searchType' => $this->searchType,
            'searchDate' => $this->searchDate,
            'searchReferralCount'=> $this->searchReferralCount,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['searchType'],
            $data['periodType'] ?? 'unknown',
            $data['countOfReferral'] ?? 0,
            $data['rangeStart'] ?? 'unknown',
            $data['rangeEnd'] ?? 'unknown',
        );
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function getSearchDate(): string
    {
        return $this->searchDate;
    }

    public function setSearchDate(string $searchDate): void
    {
        $this->searchDate = $searchDate;
    }

    public function getCountOfReferral(): int
    {
        return $this->searchReferralCount;
    }

    public function setCountOfReferral(int $searchReferralCount): void
    {
        $this->searchReferralCount = $searchReferralCount;
    }

    public function getRangeStart(): string
    {
        return $this->rangeStart;
    }

    public function setRangeStart(string $rangeStart): void
    {
        $this->rangeStart = $rangeStart;
    }

    public function getRangeEnd(): string
    {
        return $this->rangeEnd;
    }

    public function setRangeEnd(string $rangeEnd): void
    {
        $this->rangeEnd = $rangeEnd;
    }
}
