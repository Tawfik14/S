<?php

namespace App\Controller;

use App\Repository\WatchlistItemRepository;
use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class MovieController extends AbstractController
{
    #[Route('/movie/{id}-{slug}', name: 'app_movie_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        string $slug,
        SluggerInterface $slugger,
        TmdbClient $tmdb,
        WatchlistItemRepository $watchlist,
        CsrfTokenManagerInterface $csrf
    ): Response {
        $m = $tmdb->movie($id);
        $expected = strtolower($slugger->slug($m['title'] ?? 'film'));
        if ($slug !== $expected) {
            return $this->redirectToRoute('app_movie_show', ['id' => $id, 'slug' => $expected], 301);
        }

        $credits = $tmdb->credits($id);
        $cast = array_slice($credits['cast'] ?? [], 0, 12);
        $trailerKey = $tmdb->bestTrailerKey($id);

        $providersRaw = $tmdb->movieWatchProviders($id);
        $providersFR  = $providersRaw['results']['FR'] ?? null;

        $reco    = $tmdb->movieRecommendations($id);
        $similar = $tmdb->movieSimilar($id);

        $user = $this->getUser();
        $inWatchlist = false;
        if ($user && method_exists($user, 'getId')) {
            $inWatchlist = $watchlist->isInWatchlist($user->getId(), 'movie', $id);
        }
        $csrfWatchlist = $csrf->getToken('watchlist')->getValue();

        return $this->render('movie/show.html.twig', [
            'm' => $m,
            'cast' => $cast,
            'trailerKey' => $trailerKey,
            'providers' => $providersFR,
            'reco' => $reco,
            'similar' => $similar,
            'img' => $tmdb,
            'inWatchlist' => $inWatchlist,
            'csrf_watchlist' => $csrfWatchlist,
        ]);
    }
}

