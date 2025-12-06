<?php

namespace App\Http\Dto;

interface TelegramUserIdentityInterface
{
    public function getChatId(): int;
    public function getUsername(): string;
    public function getFirstName(): string;
    public function getLastName(): string;
}
