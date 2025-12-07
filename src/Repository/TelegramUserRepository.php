<?php

namespace App\Repository;

use App\Entity\TelegramUser;
use App\Repository\Trait\DoctrineOperationsTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramUser>
 */
class TelegramUserRepository extends ServiceEntityRepository
{
    use DoctrineOperationsTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramUser::class);
    }

    public function countReferrals(int $userId): int
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->select('COUNT(u.id)')
            ->where('u.referredByUser = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
