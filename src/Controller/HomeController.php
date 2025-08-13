<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TmdbClient $tmdb): Response
    {
        $genres = [
            'Action' => 28,
            'Comédie' => 35,
            'Science-fiction' => 878,
            'Horreur' => 27,
            'Animation' => 16,
            'Drame' => 18,
            'Thriller' => 53,
        ];

        $rowsByGenre = [];
        foreach ($genres as $name => $id) {
            $rowsByGenre[$name] = $tmdb->byGenre($id);
        }

        return $this->render('home/index.html.twig', [
            'trending'  => $tmdb->trending(),
            'popular'   => $tmdb->popular(),
            'topRated'  => $tmdb->topRated(),
            'upcoming'  => $tmdb->upcoming(),
            'byGenres'  => $rowsByGenre,
            'img'       => $tmdb,
        ]);
    }

    #[Route('/movie/{id}-{slug}', name: 'app_movie_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, SluggerInterface $slugger, TmdbClient $tmdb): Response
    {
        $m = $tmdb->movie($id);
        $expected = strtolower($slugger->slug($m['title'] ?? 'film'));
        if ($slug !== $expected) {
            return $this->redirectToRoute('app_movie_show', ['id' => $id, 'slug' => $expected], 301);
        }

        $credits = $tmdb->credits($id);
        $cast = array_slice($credits['cast'] ?? [], 0, 12);
        $trailerKey = $tmdb->bestTrailerKey($id);

        return $this->render('movie/show.html.twig', [
            'm' => $m,
            'cast' => $cast,
            'trailerKey' => $trailerKey,
            'img' => $tmdb,
        ]);
    }
}

