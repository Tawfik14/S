<?php

namespace App\Controller;

use App\Entity\WatchlistItem;
use App\Service\TmdbClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class WatchlistController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/ma-liste', name: 'app_watchlist', methods: ['GET'])]
    public function index(EntityManagerInterface $em, TmdbClient $tmdb): \Symfony\Component\HttpFoundation\Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        /** @var \App\Repository\WatchlistItemRepository $repo */
        $repo = $em->getRepository(WatchlistItem::class);

        $items = $repo->findByUser($user->getId());

        return $this->render('account/watchlist.html.twig', [
            'items' => $items,
            'img' => $tmdb,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/watchlist/toggle', name: 'app_watchlist_toggle', methods: ['POST'])]
    public function toggle(
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrf
    ): JsonResponse {
        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$token || !$csrf->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('watchlist', $token))) {
            return new JsonResponse(['error' => 'invalid_csrf'], 400);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'not_authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true) ?: [];
        $mediaType = $data['mediaType'] ?? '';
        $tmdbId    = (int)($data['tmdbId'] ?? 0);
        $title     = trim((string)($data['title'] ?? ''));
        $poster    = $data['poster'] ?? null;
        $backdrop  = $data['backdrop'] ?? null;

        if (!in_array($mediaType, ['movie', 'tv'], true) || $tmdbId <= 0 || $title === '') {
            return new JsonResponse(['error' => 'invalid_payload'], 400);
        }

        /** @var \App\Repository\WatchlistItemRepository $repo */
        $repo = $em->getRepository(WatchlistItem::class);

        $exists = $repo->isInWatchlist($user->getId(), $mediaType, $tmdbId);
        if ($exists) {
            $repo->removeFor($user->getId(), $mediaType, $tmdbId);
            return new JsonResponse(['status' => 'removed']);
        }

        $item = (new WatchlistItem())
            ->setUserId($user->getId())
            ->setMediaType($mediaType)
            ->setTmdbId($tmdbId)
            ->setTitle($title)
            ->setPosterPath($poster)
            ->setBackdropPath($backdrop);

        $em->persist($item);
        $em->flush();

        return new JsonResponse(['status' => 'added']);
    }
}

