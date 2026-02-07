<?php

namespace App\Service\Test;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ManageOrderService
{
    public function __construct(
        #[Autowire(service: OrderAnotherRealizationService::class)]
        private OrderInterface $order
    ) {
    }

    public function manage(): string
    {
        return $this->order->order();
    }
}
