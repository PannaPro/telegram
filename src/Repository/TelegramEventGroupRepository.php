<?php

namespace App\Repository;

use App\Entity\TelegramEventGroup;
use App\Repository\Trait\DoctrineOperationsTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramEventGroup>
 */
class TelegramEventGroupRepository extends ServiceEntityRepository
{
    use DoctrineOperationsTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramEventGroup::class);
    }

    /**
     * Find all groups that are available for event assignment
     * (groups where bot is admin, can invite users, and is active, without linked event)
     *
     * @return TelegramEventGroup[]
     */
    public function findAvailableGroups(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('App\Entity\Event', 'e', 'WITH', 'e.eventGroup = g')
            ->where('g.botIsAdmin = :true')
            ->andWhere('g.canInvite = :true')
            ->andWhere('g.isActive = :true')
            ->andWhere('e.id IS NULL')
            ->setParameter('true', true)
            ->orderBy('g.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
