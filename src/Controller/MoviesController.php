<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class MoviesController extends AbstractController
{
    #[Route('/', name: 'app_movies_index')] // Accueil + corrige le lien du menu
    #[Route('/films', name: 'app_movies_list')] // Alias optionnel
    public function index(TmdbClient $tmdb): Response
    {
        return $this->render('movies/index.html.twig', [
            'trending'  => $tmdb->trending(),
            'popular'   => $tmdb->popular(),
            'topRated'  => $tmdb->topRated(),
            'upcoming'  => $tmdb->upcoming(),
            'img'       => $tmdb, // pour img.img() dans Twig
        ]);
    }
}

