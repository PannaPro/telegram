<?php

namespace App\Service\Telegram\Admin\AdminAction\ReferralSearch;

use App\Service\Telegram\Enum\TelegramDefaultValue;

class ReferralSearchContextDto
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

    public static function fromArray(array $data): self
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

    /**
     * @return int
     */
    public function getChatId(): int
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function getSearchType(): string
    {
        return $this->searchType;
    }

    /**
     * @return string
     */
    public function getSearchDate(): string
    {
        return $this->searchDate;
    }

    /**
     * @param string $searchDate
     */
    public function setSearchDate(string $searchDate): void
    {
        $this->searchDate = $searchDate;
    }

    /**
     * @return int
     */
    public function getCountOfReferral(): int
    {
        return $this->searchReferralCount;
    }

    /**
     * @param int $searchReferralCount
     */
    public function setCountOfReferral(int $searchReferralCount): void
    {
        $this->searchReferralCount = $searchReferralCount;
    }

    /**
     * @return string
     */
    public function getRangeStart(): string
    {
        return $this->rangeStart;
    }

    /**
     * @param string $rangeStart
     */
    public function setRangeStart(string $rangeStart): void
    {
        $this->rangeStart = $rangeStart;
    }

    /**
     * @return string
     */
    public function getRangeEnd(): string
    {
        return $this->rangeEnd;
    }

    /**
     * @param string $rangeEnd
     */
    public function setRangeEnd(string $rangeEnd): void
    {
        $this->rangeEnd = $rangeEnd;
    }
}
