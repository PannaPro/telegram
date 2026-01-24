<?php

namespace App\MessageHandler;

use App\Message\ManageEventStatusMessage;
use App\Service\Scheduler\SchedulerManageEventService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
#[WithMonologChannel('event_scheduler')]
class ManageEventStatusMessageHandler
{
    public function __construct(
        private SchedulerManageEventService $schedulerManageEventService,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ManageEventStatusMessage $message): void
    {
        $this->logger->info('Event scheduler task started', [
            'triggered_at' => $message->getTriggeredAt()->format('Y-m-d H:i:s'),
        ]);

        $this->schedulerManageEventService->updateEventStatuses();

        $this->logger->info('Event scheduler task completed', [
            'completed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
