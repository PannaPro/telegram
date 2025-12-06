<?php

namespace App\Http\Dto;

class UnknownTypePayload extends AbstractPayload
{
    public int $update_id;

    public function getChatId(): int
    {
        return 0;
    }

    public function getUsername(): string
    {
        return 'unknown';
    }

    public function getFirstName(): string
    {
        return 'unknown';
    }

    public function getLastName(): string
    {
        return 'unknown';
    }
}
