<?php

namespace App\Service\Scheduler;

use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('event_scheduler')]
class SchedulerManageEventService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function updateEventStatuses(): void
    {
        $now = new DateTimeImmutable();

        // Find events that should be activated (start time has come)
        $eventsToActivate = $this->eventRepository->createQueryBuilder('e')
            ->where('e.isActive = :inactive')
            ->andWhere('e.periodFrom <= :now')
            ->andWhere('e.periodTo > :now')
            ->setParameter('inactive', false)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        foreach ($eventsToActivate as $event) {
            $event->setActive(true);

            $this->logger->info('Event activated', [
                'event_id' => $event->getId(),
                'event_name' => $event->getName(),
                'new_status' => 'active',
                'period_from' => $event->getPeriodFrom()->format('Y-m-d H:i:s'),
                'period_to' => $event->getPeriodTo()->format('Y-m-d H:i:s'),
            ]);
        }

        // Find events that should be deactivated (end time has passed)
        $eventsToDeactivate = $this->eventRepository->createQueryBuilder('e')
            ->where('e.isActive = :active')
            ->andWhere('e.periodTo <= :now')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        foreach ($eventsToDeactivate as $event) {
            $event->setActive(false);

            $this->logger->info('Event deactivated', [
                'event_id' => $event->getId(),
                'event_name' => $event->getName(),
                'new_status' => 'inactive',
                'period_from' => $event->getPeriodFrom()->format('Y-m-d H:i:s'),
                'period_to' => $event->getPeriodTo()->format('Y-m-d H:i:s'),
            ]);
        }

        // Flush all changes at once
        if (count($eventsToActivate) > 0 || count($eventsToDeactivate) > 0) {
            $this->entityManager->flush();

            $this->logger->info('Event statuses updated', [
                'activated_count' => count($eventsToActivate),
                'deactivated_count' => count($eventsToDeactivate),
                'total_updated' => count($eventsToActivate) + count($eventsToDeactivate),
            ]);
        } else {
            $this->logger->debug('No events to update');
        }
    }
}
