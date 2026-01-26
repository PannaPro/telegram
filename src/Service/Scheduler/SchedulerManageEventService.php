<?php

namespace App\Service\Scheduler;

use App\Entity\Event;
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

        $eventsToProcess = [
            'activate' => $this->eventRepository->createQueryBuilder('e')
                ->where('e.isActive = :inactive')
                ->andWhere('e.periodFrom <= :now')
                ->andWhere('e.periodTo > :now')
                ->setParameter('inactive', false)
                ->setParameter('now', $now)
                ->getQuery()
                ->getResult(),

            'deactivate' => $this->eventRepository->createQueryBuilder('e')
                ->where('e.isActive = :active')
                ->andWhere('e.periodTo <= :now')
                ->setParameter('active', true)
                ->setParameter('now', $now)
                ->getQuery()
                ->getResult(),
        ];

        $totalUpdated = 0;

        foreach ($eventsToProcess as $action => $events) {
            /** @var Event $event */

            foreach ($events as $event) {
                $newStatus = $action === 'activate';
                $event->setIsActive($newStatus);

                $this->logger->info(sprintf(
                    'Event %s: [%d] %s — new status: %s at %s',
                    $action === 'activate' ? 'activated' : 'deactivated',
                    $event->getId(),
                    $event->getName(),
                    $newStatus ? 'active' : 'inactive',
                    $now->format('Y-m-d H:i:s')
                ));
            }

            $totalUpdated += count($events);
        }

        if ($totalUpdated > 0) {
            $this->entityManager->flush();
        } else {
            $this->logger->info('No events to update', [
                'timestamp' => $now->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
