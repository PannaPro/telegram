<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;

class CreateEventContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public ?int $eventTypeId = null,
        public ?string $eventTypeName = null,
        public ?string $name = null,
        public ?string $periodFromDate = null,
        public ?string $periodFromTime = null,
        public ?string $periodToDate = null,
        public ?string $periodToTime = null,
        public ?string $partnerChanelLink = null,
        public int $step = 1,
        public bool $blockContext = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'chatId' => $this->chatId,
            'eventTypeId' => $this->eventTypeId,
            'eventTypeName' => $this->eventTypeName,
            'name' => $this->name,
            'periodFromDate' => $this->periodFromDate,
            'periodFromTime' => $this->periodFromTime,
            'periodToDate' => $this->periodToDate,
            'periodToTime' => $this->periodToTime,
            'partnerChanelLink' => $this->partnerChanelLink,
            'step' => $this->step,
            'blockContext' => $this->blockContext,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['eventTypeId'] ?? null,
            $data['eventTypeName'] ?? null,
            $data['name'] ?? null,
            $data['periodFromDate'] ?? null,
            $data['periodFromTime'] ?? null,
            $data['periodToDate'] ?? null,
            $data['periodToTime'] ?? null,
            $data['partnerChanelLink'] ?? null,
            $data['step'] ?? 1,
            $data['blockContext'] ?? true,
        );
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getEventTypeId(): ?int
    {
        return $this->eventTypeId;
    }

    public function setEventTypeId(?int $eventTypeId): void
    {
        $this->eventTypeId = $eventTypeId;
    }

    public function getEventTypeName(): ?string
    {
        return $this->eventTypeName;
    }

    public function setEventTypeName(?string $eventTypeName): void
    {
        $this->eventTypeName = $eventTypeName;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPeriodFromDate(): ?string
    {
        return $this->periodFromDate;
    }

    public function setPeriodFromDate(?string $periodFromDate): void
    {
        $this->periodFromDate = $periodFromDate;
    }

    public function getPeriodFromTime(): ?string
    {
        return $this->periodFromTime;
    }

    public function setPeriodFromTime(?string $periodFromTime): void
    {
        $this->periodFromTime = $periodFromTime;
    }

    public function getPeriodToDate(): ?string
    {
        return $this->periodToDate;
    }

    public function setPeriodToDate(?string $periodToDate): void
    {
        $this->periodToDate = $periodToDate;
    }

    public function getPeriodToTime(): ?string
    {
        return $this->periodToTime;
    }

    public function setPeriodToTime(?string $periodToTime): void
    {
        $this->periodToTime = $periodToTime;
    }

    public function getPartnerChanelLink(): ?string
    {
        return $this->partnerChanelLink;
    }

    public function setPartnerChanelLink(?string $partnerChanelLink): void
    {
        $this->partnerChanelLink = $partnerChanelLink;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): void
    {
        $this->step = $step;
    }

    public function isBlockContext(): bool
    {
        return $this->blockContext;
    }

    public function setBlockContext(bool $blockContext): void
    {
        $this->blockContext = $blockContext;
    }

    public function getFormattedText(): string
    {
        $text = "Создание события. Категория: {$this->eventTypeName}";
        
        if ($this->periodFromDate && $this->periodFromTime) {
            $text .= ". Дата начала: {$this->periodFromDate} {$this->periodFromTime}";
        }
        
        if ($this->periodToDate && $this->periodToTime) {
            $text .= ". Дата окончания: {$this->periodToDate} {$this->periodToTime}";
        }
        
        if ($this->partnerChanelLink) {
            $text .= ". Ссылка на канал партнера: {$this->partnerChanelLink}";
        }
        
        return $text;
    }
}
