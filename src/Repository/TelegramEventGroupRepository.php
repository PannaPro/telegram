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
}
