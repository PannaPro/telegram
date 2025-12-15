<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class ReferralSearchContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public string $searchType,
        public string $text,
        public ?string $dateType = TelegramDefaultValue::UNKNOWN,
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
            'text' => $this->text,
            'dataType' => $this->dateType,
            'searchDate' => $this->searchDate,
            'searchReferralCount'=> $this->searchReferralCount,
            'rangeStart' => $this->rangeStart,
            'rangeEnd' => $this->rangeEnd,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['searchType'],
            $data['text'],
            $data['dataType'] ?? 'unknown',
            $data['searchDate'] ?? 'unknown',
            $data['searchReferralCount'] ?? 0,
            $data['rangeStart'] ?? 'unknown',
            $data['rangeEnd'] ?? 'unknown',
        );
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getDateType(): string
    {
        return $this->dateType;
    }

    public function setDateType(string $dateType): void
    {
        $this->dateType = $dateType;
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
