<?php

namespace App\Service\ExceptionHandler;

class CacheException extends DomainException
{
    public function getTitle(): string
    {
        return 'Сервис не доступен';
    }

    public static function messageToClient(): self
    {
        return new self('Не удалось обработать данные. Пожалуйста, попробуйте еще раз.');
    }
}
