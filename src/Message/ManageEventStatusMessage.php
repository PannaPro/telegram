<?php

namespace App\Message;

class ManageEventStatusMessage
{
    public function __construct(
        private readonly \DateTimeImmutable $triggeredAt,
    ) {
    }

    public function getTriggeredAt(): \DateTimeImmutable
    {
        return $this->triggeredAt;
    }
}
