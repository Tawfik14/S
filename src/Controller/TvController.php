<?php

namespace App\Controller;

use App\Repository\WatchlistItemRepository;
use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class TvController extends AbstractController
{
    #[Route('/tv/{id}-{slug}', name: 'app_tv_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        string $slug,
        TmdbClient $tmdb,
        SluggerInterface $slugger,
        WatchlistItemRepository $watchlist,
        CsrfTokenManagerInterface $csrf
    ): Response {
        $tv = $tmdb->tv($id);
        $expected = strtolower($slugger->slug($tv['name'] ?? 'serie'));
        if ($slug !== $expected) {
            return $this->redirectToRoute('app_tv_show', ['id' => $id, 'slug' => $expected], 301);
        }

        $credits = $tmdb->tvCredits($id);
        $cast = array_slice($credits['cast'] ?? [], 0, 12);
        $trailerKey = $tmdb->bestTvTrailerKey($id);

        $providersRaw = $tmdb->tvWatchProviders($id);
        $providersFR  = $providersRaw['results']['FR'] ?? null;

        $reco    = $tmdb->tvRecommendations($id);
        $similar = $tmdb->tvSimilar($id);

        $user = $this->getUser();
        $inWatchlist = false;
        if ($user && method_exists($user, 'getId')) {
            $inWatchlist = $watchlist->isInWatchlist($user->getId(), 'tv', $id);
        }
        $csrfWatchlist = $csrf->getToken('watchlist')->getValue();

        return $this->render('tv/show.html.twig', [
            'tv' => $tv,
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

    #[Route('/tv/{id}/season/{season<\d+>}', name: 'app_tv_season')]
    public function season(int $id, int $season, TmdbClient $tmdb): Response
    {
        $seasonData = $tmdb->tvSeason($id, $season);
        return $this->render('tv/_season.html.twig', [
            'season' => $seasonData ?: ['season_number' => $season, 'episodes' => []],
            'img' => $tmdb,
        ]);
    }
}

