<?php

namespace App\Http\Dto;

class NewChatTitleTelegramPayload extends AbstractPayload
{
    public array $message;

    public function getChatId(): int
    {
        return $this->message['chat']['id'] ?? 0;
    }

    public function getNewTitle(): string
    {
        return $this->message['new_chat_title'] ?? '';
    }

    public function getChatType(): string
    {
        return $this->message['chat']['type'] ?? 'group';
    }

    public function getLastName(): string
    {
        return $this->message['from']['last_name'] ?? 'unknown';
    }

    public function getUsername(): string
    {
        return $this->message['from']['username'] ?? 'unknown';
    }

    public function getFirstName(): string
    {
        return $this->message['from']['first_name'] ?? 'unknown';
    }
}
