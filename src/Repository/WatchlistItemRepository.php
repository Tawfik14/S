<?php

namespace App\Repository;

use App\Entity\WatchlistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WatchlistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WatchlistItem::class);
    }

    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.userId = :u')
            ->setParameter('u', $userId)
            ->orderBy('w.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function isInWatchlist(int $userId, string $mediaType, int $tmdbId): bool
    {
        return (bool) $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->andWhere('w.userId = :u')
            ->andWhere('w.mediaType = :t')
            ->andWhere('w.tmdbId = :i')
            ->setParameter('u', $userId)
            ->setParameter('t', $mediaType)
            ->setParameter('i', $tmdbId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function removeFor(int $userId, string $mediaType, int $tmdbId): void
    {
        $this->createQueryBuilder('w')
            ->delete()
            ->andWhere('w.userId = :u')
            ->andWhere('w.mediaType = :t')
            ->andWhere('w.tmdbId = :i')
            ->setParameter('u', $userId)
            ->setParameter('t', $mediaType)
            ->setParameter('i', $tmdbId)
            ->getQuery()
            ->execute();
    }
}

