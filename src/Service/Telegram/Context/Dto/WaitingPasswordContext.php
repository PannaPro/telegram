<?php

namespace App\Service\Telegram\Context\Dto;

use App\Service\Telegram\Context\ContextInterface;

class WaitingPasswordContext implements ContextInterface
{
    public function __construct(
        public int $chatId,
        public bool $isWaitingPassword,
    ) {
    }

    public function toArray(): array
    {
        return [
            'chatId' => $this->chatId,
            'isWaitingPassword' => $this->isWaitingPassword,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new self(
            $data['chatId'],
            $data['isWaitingPassword'],
        );
    }
}
