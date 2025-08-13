<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class SeriesController extends AbstractController
{
    #[Route('/series', name: 'app_series')]
    public function index(TmdbClient $tmdb): Response
    {
        // Genres TV (TMDB)
        $genres = [
            'Action & Aventure' => 10759,
            'Animation'         => 16,
            'Comédie'           => 35,
            'Crime'             => 80,
            'Documentaire'      => 99,
            'Drame'             => 18,
            'Famille'           => 10751,
            'Kids'              => 10762,
            'Mystère'           => 9648,
            'Sci-Fi & Fantasy'  => 10765,
            'Guerre & Politique'=> 10768,
            'Western'           => 37,
        ];

        $byGenres = [];
        foreach ($genres as $label => $id) {
            $byGenres[$label] = $tmdb->tvByGenre($id);
        }

        return $this->render('pages/series.html.twig', [
            'hero'        => $tmdb->tvOnTheAir(),     // gros carrousel
            'popular'     => $tmdb->tvPopular(),
            'topRated'    => $tmdb->tvTopRated(),
            'airingToday' => $tmdb->tvAiringToday(),
            'byGenres'    => $byGenres,
            'img'         => $tmdb,                   // pour img.img() dans Twig
        ]);
    }
}

