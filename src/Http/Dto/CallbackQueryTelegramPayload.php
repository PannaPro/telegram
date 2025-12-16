<?php

namespace App\Http\Dto;

class CallbackQueryTelegramPayload extends AbstractPayload
{
    public array $callback_query;

    public function getCallbackQueryId(): string
    {
        return $this->callback_query['id'] ?? '';
    }

    public function getFromId(): int
    {
        return $this->callback_query['from']['id'] ?? 0;
    }

    public function getUsername(): string
    {
        return $this->callback_query['from']['username'] ?? 'unknown';
    }

    public function getFirstName(): string
    {
        return $this->callback_query['from']['first_name'] ?? 'unknown';
    }

    public function getLastName(): string
    {
        return $this->callback_query['from']['last_name'] ?? 'unknown';
    }

    public function getChatId(): int
    {
        return $this->callback_query['message']['chat']['id'] ?? 0;
    }

    public function getChatType(): string
    {
        return $this->callback_query['message']['chat']['type'] ?? 'unknown';
    }

    public function getMessageId(): int
    {
        return $this->callback_query['message']['message_id'] ?? 0;
    }

    public function getText(): ?string
    {
        return $this->callback_query['message']['text'] ?? null;
    }

    public function getCallbackData(): string
    {
        return $this->callback_query['data'] ?? 'unknown';
    }
}
