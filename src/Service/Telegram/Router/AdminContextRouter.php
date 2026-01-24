<?php

namespace App\Service\Telegram\Router;

use App\Http\Dto\AbstractPayload;
use App\Service\Telegram\Admin\Command\Context\CreateEventCommandHandler;
use App\Service\Telegram\Admin\Command\Context\ManageEventCommandHandler;
use App\Service\Telegram\Admin\Command\Context\ReferralSearchCommandHandler;
use App\Service\Telegram\Admin\Command\Context\WaitingPasswordCommandHandler;
use App\Service\Telegram\Context\ContextInterface;
use App\Service\Telegram\Context\Dto\CreateEventContext;
use App\Service\Telegram\Context\Dto\ManageEventContext;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Context\Dto\WaitingPasswordContext;

class AdminContextRouter
{
    public function __construct(
        private ReferralSearchCommandHandler $referralSearchCommandHandler,
        private WaitingPasswordCommandHandler $waitingPasswordCommandHandler,
        private CreateEventCommandHandler $createEventCommandHandler,
        private ManageEventCommandHandler $manageEventCommandHandler,
    ) {
    }

    public function route(AbstractPayload $payload, ContextInterface $context): void
    {
        switch (true) {
            case $context instanceof WaitingPasswordContext:
                $this->waitingPasswordCommandHandler->handleCommand($payload);
                break;
            case $context instanceof ReferralSearchContext:
                $this->referralSearchCommandHandler->handleCommand($payload, $context);
                break;
            case $context instanceof CreateEventContext:
                $this->createEventCommandHandler->handleCommand($payload, $context);
                break;
            case $context instanceof ManageEventContext:
                $this->manageEventCommandHandler->handleCommand($payload, $context);
                break;
            default:
                throw new \Exception('Неизвестный контекст админа');
        }
    }
}
