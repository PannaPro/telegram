<?php

namespace App\Service\Telegram\Context;

interface ContextInterface
{
    public function toArray(): array;

    /**
     * Статический метод для восстановления DTO из массива
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static;
}
