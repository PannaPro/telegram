<?php

namespace App\Service\ExceptionHandle;

class TelegramBotApiException extends DomainExternalException
{
    public function getTitle(): string
    {
        return 'Сервис не доступен';
    }

    public static function connectionFailed(string $errorText): self
    {
        return new self($errorText);
    }
}
