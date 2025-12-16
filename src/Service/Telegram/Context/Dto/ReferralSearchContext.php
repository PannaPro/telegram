<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class ReferralSearchContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public ?string $searchType = null,
        public ?string $textType = null,
        public ?string $dateType = null,
        public ?string $dateText = null,
        public ?string $searchDate = null,
        public ?string $rangeStart = null,
        public ?string $rangeEnd = null,
        public ?int $count = null,
        public bool $blockContext = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'chatId' => $this->chatId,
            'searchType' => $this->searchType,
            'textType' => $this->textType,
            'dateType' => $this->dateType,
            'dateText' => $this->dateText,
            'searchDate' => $this->searchDate,
            'rangeStart' => $this->rangeStart,
            'rangeEnd' => $this->rangeEnd,
            'count'=> $this->count,
            'blockContext'=> $this->blockContext,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['searchType'],
            $data['textType'],
            $data['dateType'],
            $data['dateText'],
            $data['searchDate'],
            $data['rangeStart'],
            $data['rangeEnd'],
            $data['count'],
            $data['blockContext']
        );
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getSearchType(): ?string
    {
        return $this->searchType;
    }

    public function setSearchType(?string $searchType): void
    {
        $this->searchType = $searchType;
    }

    public function getTextType(): ?string
    {
        return $this->textType;
    }

    public function setTextType(?string $textType): void
    {
        $this->textType = $textType;
    }

    public function getDateType(): ?string
    {
        return $this->dateType;
    }

    public function setDateType(?string $dateType): void
    {
        $this->dateType = $dateType;
    }

    public function getDateText(): ?string
    {
        return $this->dateText;
    }

    public function setDateText(?string $dateText): void
    {
        $this->dateText = $dateText;
    }

    public function getSearchDate(): ?string
    {
        return $this->searchDate;
    }

    public function setSearchDate(?string $searchDate): void
    {
        $this->searchDate = $searchDate;
    }

    public function getRangeStart(): ?string
    {
        return $this->rangeStart;
    }

    public function setRangeStart(?string $rangeStart): void
    {
        $this->rangeStart = $rangeStart;
    }

    public function getRangeEnd(): ?string
    {
        return $this->rangeEnd;
    }

    public function setRangeEnd(?string $rangeEnd): void
    {
        $this->rangeEnd = $rangeEnd;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

    public function isBlockContext(): bool
    {
        return $this->blockContext;
    }

    public function setBlockContext(bool $blockContext): void
    {
        $this->blockContext = $blockContext;
    }
}
