<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RatingController extends AbstractController
{
    #[Route('/api/rating/summary', name: 'app_rating_summary', methods: ['GET'])]
    public function summary(Request $req, RatingRepository $repo): JsonResponse
    {
        $mediaType = $req->query->get('mediaType', '');
        $tmdbId = (int)$req->query->get('tmdbId', 0);

        if (!in_array($mediaType, ['movie','tv'], true) || $tmdbId <= 0) {
            return new JsonResponse(['error' => 'invalid_query'], 400);
        }

        $stats = $repo->getStats($mediaType, $tmdbId);

        $your = null;
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if ($user) {
            $r = $repo->findOneByUserAndMedia($user->getId(), $mediaType, $tmdbId);
            if ($r) $your = $r->getValue();
        }

        return new JsonResponse([
            'avg' => round($stats['avg'], 1),
            'count' => $stats['count'],
            'your' => $your,
        ]);
    }

    #[Route('/api/rating', name: 'app_rating_set', methods: ['POST'])]
    public function set(
        Request $req,
        RatingRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        // CSRF
        $token = $req->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('rating', (string)$token)) {
            return new JsonResponse(['error' => 'invalid_csrf'], 400);
        }

        // Auth
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'not_authenticated'], 401);
        }

        $data = json_decode($req->getContent(), true) ?: [];
        $mediaType = $data['mediaType'] ?? '';
        $tmdbId    = (int)($data['tmdbId'] ?? 0);
        $value     = (int)($data['value'] ?? 0);

        if (!in_array($mediaType, ['movie','tv'], true) || $tmdbId <= 0 || $value < 1 || $value > 10) {
            return new JsonResponse(['error' => 'invalid_payload'], 400);
        }

        $existing = $repo->findOneByUserAndMedia($user->getId(), $mediaType, $tmdbId);

        if ($existing) {
            $existing->setValue($value)->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
        } else {
            $r = (new Rating())
                ->setUserId($user->getId())
                ->setMediaType($mediaType)
                ->setTmdbId($tmdbId)
                ->setValue($value);
            $em->persist($r);
            $em->flush();
        }

        // Retourne stats à jour
        $stats = $repo->getStats($mediaType, $tmdbId);
        return new JsonResponse([
            'status' => 'ok',
            'avg' => round($stats['avg'], 1),
            'count' => $stats['count'],
            'your' => $value,
        ]);
    }
}

