<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;

class ManageEventContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public ?int $eventId = null,
        public ?string $eventName = null,
        public ?string $action = null, // 'view', 'edit', 'delete', 'toggle_active'
        public ?string $editField = null, // 'start_date', 'end_date', 'group', 'partner_link'
        public int $step = 1,
        public bool $blockContext = true,
    ) {
    }

    public function toArray(): array
    {
        return [
            'chatId' => $this->chatId,
            'eventId' => $this->eventId,
            'eventName' => $this->eventName,
            'action' => $this->action,
            'editField' => $this->editField,
            'step' => $this->step,
            'blockContext' => $this->blockContext,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['eventId'] ?? null,
            $data['eventName'] ?? null,
            $data['action'] ?? null,
            $data['editField'] ?? null,
            $data['step'] ?? 1,
            $data['blockContext'] ?? true,
        );
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(?int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getEditField(): ?string
    {
        return $this->editField;
    }

    public function setEditField(?string $editField): void
    {
        $this->editField = $editField;
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
}
