<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function findOneByUserAndMedia(int $userId, string $mediaType, int $tmdbId): ?Rating
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.userId = :u')->setParameter('u', $userId)
            ->andWhere('r.mediaType = :t')->setParameter('t', $mediaType)
            ->andWhere('r.tmdbId = :i')->setParameter('i', $tmdbId)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return array{avg: float, count: int} */
    public function getStats(string $mediaType, int $tmdbId): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('AVG(r.value) AS avg', 'COUNT(r.id) AS cnt')
            ->andWhere('r.mediaType = :t')->setParameter('t', $mediaType)
            ->andWhere('r.tmdbId = :i')->setParameter('i', $tmdbId);
        $row = $qb->getQuery()->getSingleResult();

        return [
            'avg' => $row['avg'] ? (float)$row['avg'] : 0.0,
            'count' => (int)$row['cnt'],
        ];
    }
}

