<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieController extends AbstractController
{
    #[Route('/', name: 'app_movies_index')]
    public function index(): Response
    {
        $movies = $this->getMovies();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies,
        ]);
    }

    #[Route('/movie/{slug}', name: 'app_movies_show')]
    public function show(string $slug): Response
    {
        $movies = $this->getMovies();
        $movie = null;

        foreach ($movies as $m) {
            if ($m['slug'] === $slug) {
                $movie = $m;
                break;
            }
        }

        if ($movie === null) {
            throw $this->createNotFoundException('Film introuvable');
        }

        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getMovies(): array
    {
        return [
            [
                'slug' => 'inception',
                'title' => 'Inception',
                'year' => 2010,
                'genres' => ['Science‑fiction', 'Thriller'],
                'runtime' => 148,
                'rating' => 8.8,
                'poster' => 'https://picsum.photos/seed/inception/500/750',
                'backdrop' => 'https://picsum.photos/seed/inceptionb/1200/600',
                'tagline' => 'Votre esprit est la scène du crime.',
                'synopsis' => "Dom Cobb, voleur expérimenté, s'introduit dans les rêves pour extraire des secrets. Une mission impossible pourrait lui rendre sa vie, mais au prix de la réalité.",
            ],
            [
                'slug' => 'interstellar',
                'title' => 'Interstellar',
                'year' => 2014,
                'genres' => ['Science‑fiction', 'Aventure'],
                'runtime' => 169,
                'rating' => 8.6,
                'poster' => 'https://picsum.photos/seed/interstellar/500/750',
                'backdrop' => 'https://picsum.photos/seed/interstellarb/1200/600',
                'tagline' => 'Le destin de l’humanité est parmi les étoiles.',
                'synopsis' => "Un groupe d'explorateurs voyage à travers un trou de ver pour assurer la survie de l'humanité, laissant derrière eux la Terre mourante.",
            ],
            [
                'slug' => 'the-dark-knight',
                'title' => 'The Dark Knight',
                'year' => 2008,
                'genres' => ['Action', 'Crime', 'Drame'],
                'runtime' => 152,
                'rating' => 9.0,
                'poster' => 'https://picsum.photos/seed/darkknight/500/750',
                'backdrop' => 'https://picsum.photos/seed/darkknightb/1200/600',
                'tagline' => 'Pourquoi si sérieux ?',
                'synopsis' => "Batman affronte le Joker, un criminel imprévisible qui plonge Gotham dans le chaos, forçant le Chevalier Noir à franchir ses propres limites.",
            ],
        ];
    }
}