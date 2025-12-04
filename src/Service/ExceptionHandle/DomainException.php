<?php

namespace App\Service\ExceptionHandle;

abstract class DomainException extends \DomainException
{
    protected function __construct(string $detail) {
        parent::__construct($detail);
    }

    /**
     * Значение для title из RFC 7807 стандарта (для ошибок)
     *
     * @return string
     */
    abstract public function getTitle(): string;

    /**
     * Значение для detail из RFC 7807 стандарта (для ошибок)
     *
     * @return string
     */
    final public function getDetail(): string
    {
        return parent::getMessage();
    }
}
