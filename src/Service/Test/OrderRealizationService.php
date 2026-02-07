<?php

namespace App\Service\Test;

class OrderRealizationService implements OrderInterface
{
    public function order(): string
    {
        return 'order-order';
    }
}
