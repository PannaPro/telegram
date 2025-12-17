<?php

namespace App\Repository;

use App\Entity\TelegramUser;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TelegramUser>
 */
class ReferralSearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TelegramUser::class);
    }

    public function findReferralBySearch(ReferralSearchContext $contextFilters): array
    {
        $qb = $this->createQueryBuilder('u');
        $expr = $qb->expr();

        $qb
            ->select('u.chatId', 'u.username', 'COUNT(r.id) referralCount')
            ->leftJoin('u.referredBy', 'r')
            ->where(
                $expr->andX(
                    $expr->eq('u.participant', ':true'),
                    $expr->eq('u.isActive', ':true'),
                    $expr->eq('r.isActive', ':true'),
                    $expr->eq('u.isAdmin', ':false'),
                    $expr->eq('r.isAdmin', ':false'),
                )
            )
            ->groupBy('u.id')
            ->having('COUNT(r.id) >= :count')
            ->setParameter('true', 1)
            ->setParameter('false', 0)
            ->setParameter('count', $contextFilters->getCount());

        if ($contextFilters->getRangeStart() && $contextFilters->getRangeEnd()) {
            $qb
                ->andWhere($expr->between('r.createdAt', ':from', ':to'))
                ->setParameter('from', $contextFilters->getRangeStart())
                ->setParameter('to', $contextFilters->getRangeEnd());
        }

        if ($contextFilters->getSearchDate()) {
            $qb
                ->andWhere($expr->eq('DATE(r.createdAt)', ':date'))
                ->setParameter('date', $contextFilters->getSearchDate());
        }

        $status = $contextFilters->getSearchType();
        if ($status) {
            if ($status === 'participant_cd_referral') {
                $qb->andWhere(
                    $expr->andX(
                        $expr->eq('r.participant', ':true'),
//                    $expr->eq('r.participant', ':true'),
                    )
                );
            } elseif ($status === 'participant_referral') {
                $qb->andWhere($expr->eq('r.participant', ':true'));
            }
        }

        $qb->orderBy('COUNT(r.id)', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
