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
                'slug' => 'arrival',
                'title' => 'Arrival',
                'year' => 2016,
                'genres' => ['Science‑fiction', 'Drame'],
                'runtime' => 116,
                'rating' => 7.9,
                'poster' => 'https://picsum.photos/seed/arrival/500/750',
                'backdrop' => 'https://picsum.photos/seed/arrivalb/1200/600',
                'tagline' => 'Pourquoi sont‑ils venus ?',
                'synopsis' => "Une linguiste est recrutée pour communiquer avec des extraterrestres mystérieux, révélant une perspective radicale sur le temps et la vie.",
            ],
            [
                'slug' => 'the-matrix',
                'title' => 'The Matrix',
                'year' => 1999,
                'genres' => ['Science‑fiction', 'Action'],
                'runtime' => 136,
                'rating' => 8.7,
                'poster' => 'https://picsum.photos/seed/matrix/500/750',
                'backdrop' => 'https://picsum.photos/seed/matrixb/1200/600',
                'tagline' => 'Bienvenue dans le désert du réel.',
                'synopsis' => "Un hacker découvre l'horrible vérité et mène une révolte contre des machines contrôlant l'humanité.",
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
            [
                'slug' => 'mad-max-fury-road',
                'title' => 'Mad Max: Fury Road',
                'year' => 2015,
                'genres' => ['Action', 'Aventure'],
                'runtime' => 120,
                'rating' => 8.1,
                'poster' => 'https://picsum.photos/seed/madmax/500/750',
                'backdrop' => 'https://picsum.photos/seed/madmaxb/1200/600',
                'tagline' => 'Que la route te soit favorable.',
                'synopsis' => "Dans un désert post‑apocalyptique, Max et Furiosa fuient un tyran à travers une course effrénée.",
            ],
            [
                'slug' => 'john-wick',
                'title' => 'John Wick',
                'year' => 2014,
                'genres' => ['Action', 'Thriller'],
                'runtime' => 101,
                'rating' => 7.4,
                'poster' => 'https://picsum.photos/seed/johnwick/500/750',
                'backdrop' => 'https://picsum.photos/seed/johnwickb/1200/600',
                'tagline' => 'Ne contrarie pas le Baba Yaga.',
                'synopsis' => "Un tueur à gages légendaire reprend du service pour se venger, déclenchant une guerre criminelle.",
            ],
            [
                'slug' => 'gladiator',
                'title' => 'Gladiator',
                'year' => 2000,
                'genres' => ['Action', 'Drame'],
                'runtime' => 155,
                'rating' => 8.5,
                'poster' => 'https://picsum.photos/seed/gladiator/500/750',
                'backdrop' => 'https://picsum.photos/seed/gladiatorb/1200/600',
                'tagline' => 'Le général devenu esclave. L’esclave devenu gladiateur.',
                'synopsis' => "Trahi par l'Empire, Maximus cherche justice dans l'arène, capturant le cœur du peuple.",
            ],
            [
                'slug' => 'fight-club',
                'title' => 'Fight Club',
                'year' => 1999,
                'genres' => ['Drame'],
                'runtime' => 139,
                'rating' => 8.8,
                'poster' => 'https://picsum.photos/seed/fightclub/500/750',
                'backdrop' => 'https://picsum.photos/seed/fightclubb/1200/600',
                'tagline' => 'La première règle est…',
                'synopsis' => "Un homme en crise identitaire rencontre Tyler Durden et fonde un club de combat clandestin.",
            ],
            [
                'slug' => 'the-godfather',
                'title' => 'The Godfather',
                'year' => 1972,
                'genres' => ['Drame', 'Crime'],
                'runtime' => 175,
                'rating' => 9.2,
                'poster' => 'https://picsum.photos/seed/godfather/500/750',
                'backdrop' => 'https://picsum.photos/seed/godfatherb/1200/600',
                'tagline' => 'Je vais lui faire une offre qu’il ne pourra pas refuser.',
                'synopsis' => "La saga de la famille Corleone, entre loyauté, pouvoir et trahisons.",
            ],
            [
                'slug' => 'whiplash',
                'title' => 'Whiplash',
                'year' => 2014,
                'genres' => ['Drame', 'Musique'],
                'runtime' => 106,
                'rating' => 8.5,
                'poster' => 'https://picsum.photos/seed/whiplash/500/750',
                'backdrop' => 'https://picsum.photos/seed/whiplashb/1200/600',
                'tagline' => 'Le talent ne suffit pas.',
                'synopsis' => "Un jeune batteur est poussé à l’extrême par un professeur impitoyable pour atteindre la perfection.",
            ],
            [
                'slug' => 'parasite',
                'title' => 'Parasite',
                'year' => 2019,
                'genres' => ['Drame', 'Thriller'],
                'runtime' => 132,
                'rating' => 8.6,
                'poster' => 'https://picsum.photos/seed/parasite/500/750',
                'backdrop' => 'https://picsum.photos/seed/parasiteb/1200/600',
                'tagline' => 'L’invasion est en cours.',
                'synopsis' => "Deux familles que tout oppose se rencontrent, déclenchant une série d’événements inattendus.",
            ],
        ];
    }
}
