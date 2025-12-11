<?php

namespace App\Repository;

use App\Entity\TelegramUser;
use App\Repository\Trait\DoctrineOperationsTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

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

    /**
     * @return array{
     *     total: int,
     *     today: int,
     *     week: int
     * }
     */
    public function findReferralsStatistics(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
            SELECT
            COUNT(*) FILTER (
                WHERE chat_id > 0
                ) AS users_total,

            COUNT(*) FILTER (
                WHERE referred_by_id IS NOT NULL
                    AND participant = TRUE
                    AND chat_id > 0
                ) AS referrals_total,

            COUNT(*) FILTER (
                WHERE referred_by_id IS NOT NULL
                    AND participant = TRUE
                    AND chat_id > 0
                    AND created_at::date = CURRENT_DATE
                ) AS referrals_today,

            COUNT(*) FILTER (
                WHERE referred_by_id IS NOT NULL
                    AND participant = TRUE
                    AND chat_id > 0
                    AND created_at >= NOW() - INTERVAL '7 days'
                ) AS referrals_week
            FROM telegram_user;
        SQL;

        try {
            $result = $connection->executeQuery($sql)->fetchAssociative();

            return [
                'all' => (int) $result['users_total'],
                'total' => (int) $result['referrals_total'],
                'today' => (int) $result['referrals_today'],
                'week'  => (int) $result['referrals_week'],
            ];
        } catch (Exception) {
            return [
                'all' => 0,
                'total' => 0,
                'today' => 0,
                'week'  => 0,
            ];
        }
    }
}
