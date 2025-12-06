<?php

namespace App\Service\ExceptionHandle;

class TelegramApiException extends DomainExternalException
{
    public function getTitle(): string
    {
        return 'Сервис не доступен';
    }

    public static function sendMessageFailed(): self
    {
        return new self('Send message failed.');
    }
}
