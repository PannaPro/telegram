<?php

namespace App\Http\Dto;

use DateTimeImmutable;

class MessageTelegramPayload extends AbstractPayload
{
    public array $message;

    public function getUpdateId(): int
    {
        return $this->update_id;
    }

    // -------------------- User -------------------- //
    public function getFromId(): int
    {
        return $this->message['from']['id'];
    }

    public function getFromIsBot(): bool
    {
        return $this->message['from']['is_bot'] ?? false;
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

    public function getLanguageCode(): ?string
    {
        return $this->message['from']['language_code'] ?? 'ru';
    }

    // -------------------- Chat -------------------- //
    public function getChatId(): int
    {
        return $this->message['chat']['id'] ?? 0;
    }

    public function getChatType(): string
    {
        return $this->message['chat']['type'];
    }

    public function getChatFirstName(): ?string
    {
        return $this->message['chat']['first_name'] ?? 'unknown';
    }

    public function getChatLastName(): ?string
    {
        return $this->message['chat']['last_name'] ?? 'unknown';
    }

    public function getChatUsername(): ?string
    {
        return $this->message['chat']['username'] ?? 'unknown';
    }

    // -------------------- meta -------------------- //
    public function getMessageId(): int
    {
        return $this->message['message_id'] ?? 0;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return isset($this->message['date'])
            ? (new DateTimeImmutable())->setTimestamp($this->message['date'])
            : new DateTimeImmutable();
    }

    public function getText(): string
    {
        return $this->message['text'] ?? 'UNKNOWN';
    }

    public function getVoice(): array
    {
        return $this->message['voice'] ?? [];
    }

    public function getEntities(): array
    {
        return $this->message['entities'] ?? [];
    }
}
