<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /** Upsert: 1 avis par (user, mediaType, tmdbId) */
    public function upsertForUserMedia(User $user, string $type, int $tmdbId, array $data): Review
    {
        $em = $this->getEntityManager();

        $review = $this->findOneBy([
            'user' => $user,
            'mediaType' => $type,
            'tmdbId' => $tmdbId,
        ]);

        if (!$review) {
            $review = new Review();
            $review->setUser($user)->setMediaType($type)->setTmdbId($tmdbId);
            $em->persist($review);
        }

        $review
            ->setTitle($data['title'])
            ->setBody($data['body'])
            ->setRating($data['rating'] ?? null)
            ->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();
        return $review;
    }

    /** List paginée + tri */
    public function listByMedia(string $type, int $tmdbId, string $sort, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.mediaType = :t')->setParameter('t', $type)
            ->andWhere('r.tmdbId = :id')->setParameter('id', $tmdbId)
            ->andWhere('r.isDeleted = false');

        switch ($sort) {
            case 'helpful':
                $qb->orderBy('r.helpfulCount', 'DESC')->addOrderBy('r.createdAt', 'DESC');
                break;
            case 'rating_desc':
                $qb->orderBy('r.rating', 'DESC')->addOrderBy('r.createdAt', 'DESC');
                break;
            default: // recent
                $qb->orderBy('r.createdAt', 'DESC');
        }

        return $qb->setMaxResults($limit)->setFirstResult($offset)->getQuery()->getResult();
    }

    public function countByMedia(string $type, int $tmdbId): int
    {
        return (int)$this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.mediaType = :t')->setParameter('t', $type)
            ->andWhere('r.tmdbId = :id')->setParameter('id', $tmdbId)
            ->andWhere('r.isDeleted = false')
            ->getQuery()->getSingleScalarResult();
    }
}

