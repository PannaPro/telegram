<?php

namespace App\Service\ExceptionHandle;

use DomainExternalException;

class CacheException extends DomainExternalException
{
    public function getTitle(): string
    {
        return 'Сервис не доступен';
    }

    public static function messageToClient(): self
    {
        return new self('Не удалось получить данные. Пожалуйста, попробуйте еще раз.');
    }
}
