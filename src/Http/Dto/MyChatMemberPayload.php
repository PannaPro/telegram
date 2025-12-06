<?php

namespace App\Http\Dto;

class MyChatMemberPayload extends AbstractPayload
{
    public array $my_chat_member;

    public function getChatId(): int
    {
        return $this->my_chat_member['chat']['id'];
    }

    public function getChatType(): string
    {
        return $this->my_chat_member['chat']['type'];
    }

    public function getDate(): int
    {
        return $this->my_chat_member['date'];
    }

    public function getOldChatMemberStatus(): string
    {
        return $this->my_chat_member['old_chat_member']['status'];
    }

    public function getNewChatMemberStatus(): string
    {
        return $this->my_chat_member['new_chat_member']['status'];
    }

    public function getUsername(): string
    {
        return $this->my_chat_member['chat']['username'] ?? 'unknown';
    }

    public function getFirstName(): string
    {
        return $this->my_chat_member['chat']['first_name'] ?? 'unknown';
    }

    public function getLastName(): string
    {
        return $this->my_chat_member['chat']['last_name'] ?? 'unknown';
    }

    public function getFrom(): int
    {
        return $this->my_chat_member['from']['id'] ?? 0;
    }
}
