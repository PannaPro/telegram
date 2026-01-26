<?php

namespace App\MessageHandler;

use App\Message\ManageEventStatusMessage;
use App\Service\Scheduler\SchedulerManageEventService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ManageEventStatusMessageHandler
{
    public function __construct(
        private SchedulerManageEventService $schedulerManageEventService,
    ) {
    }

    public function __invoke(ManageEventStatusMessage $message): void
    {
        $this->schedulerManageEventService->updateEventStatuses();
    }
}
