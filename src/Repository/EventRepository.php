<?php

namespace App\Repository;

use App\Entity\Event;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findCurrentAndNextEvents(): array
    {
        $qb = $this->createQueryBuilder('e');
        $expr = $qb->expr();

        $now = new DateTimeImmutable();

        $current =
            $qb->select(
                    'e.name AS eventName',
                    'e.periodFrom',
                    'e.periodTo',
                    'e.isActive'
                )
                ->andWhere('e.isActive = true')
                ->andWhere(
                    $expr->andX(
                        $expr->lte('e.periodFrom', ':now'),
                        $expr->gte('e.periodTo', ':now')
                    )
                )
                ->setParameter('now', $now)
                ->orderBy('e.periodFrom', 'ASC')
                ->getQuery()
                ->getArrayResult();

        $upcomingQb = $this->createQueryBuilder('e')
            ->select(
                'e.name AS eventName',
                'e.periodFrom',
                'e.periodTo',
                'e.isActive'
            )
            ->andWhere('e.isActive = false')
            ->andWhere('e.periodFrom > :now')
            ->setParameter('now', $now)
            ->orderBy('e.periodFrom', 'ASC')
            ->setMaxResults(5);

        $upcoming = $upcomingQb->getQuery()->getArrayResult();

        $countQb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.isActive = false')
            ->andWhere('e.periodFrom > :now')
            ->setParameter('now', $now);

        $totalUpcoming = (int)$countQb->getQuery()->getSingleScalarResult();

        return [
            'current' => $current,
            'upcoming' => $upcoming,
            'totalUpcoming' => $totalUpcoming,
        ];
    }
}
