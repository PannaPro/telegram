<?php

namespace App\Service\ExceptionHandle;

class NotFoundException extends DomainException
{
    public function getTitle(): string
    {
        return 'Запись не найдена';
    }

    public static function userNotFound(string $text = ''): self
    {
        $text = empty($text) ?
            $text :
            'Current Telegram user is not initialized. Make sure the subscriber ran before the controller.';

        return new self($text);
    }
}
