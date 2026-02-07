<?php

namespace App\Service\Test;

class OrderAnotherRealizationService implements OrderInterface
{
    public function order(): string
    {
        return 'other-realization';
    }
}
