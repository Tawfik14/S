<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

final class TvController extends AbstractController
{
    #[Route('/tv/{id}-{slug}', name: 'app_tv_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, TmdbClient $tmdb, SluggerInterface $slugger): Response
    {
        $tv = $tmdb->tv($id);
        $expected = strtolower($slugger->slug($tv['name'] ?? 'serie'));
        if ($slug !== $expected) {
            return $this->redirectToRoute('app_tv_show', ['id' => $id, 'slug' => $expected], 301);
        }

        $credits = $tmdb->tvCredits($id);
        $trailerKey = $tmdb->bestTvTrailerKey($id);

        $cast = array_slice($credits['cast'] ?? [], 0, 12);

        return $this->render('tv/show.html.twig', [
            'tv' => $tv,
            'cast' => $cast,
            'trailerKey' => $trailerKey,
            'img' => $tmdb,
        ]);
    }
    
    #[Route('/tv/{id}/season/{season<\d+>}', name: 'app_tv_season')]
public function season(int $id, int $season, TmdbClient $tmdb): Response
{
    $seasonData = $tmdb->tvSeason($id, $season);

    // petite sécurité
    if (empty($seasonData)) {
        return $this->render('tv/_season.html.twig', [
            'season' => ['season_number' => $season, 'episodes' => []],
            'img' => $tmdb,
        ]);
    }

    return $this->render('tv/_season.html.twig', [
        'season' => $seasonData,
        'img' => $tmdb,
    ]);
}

}

