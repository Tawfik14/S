<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class UpcomingController extends AbstractController
{
    #[Route('/upcoming', name: 'app_upcoming')]
    public function index(TmdbClient $tmdb): Response
    {
        // Récupère plusieurs pages pour “plein de films”
        $movies = $tmdb->upcomingPages(6); // ajuste le nombre si tu veux plus/moins

        // Groupage par mois (YYYY-MM) pour des sections stylées
        $groups = [];
        foreach ($movies as $m) {
            $date = $m['release_date'] ?? null;
            if (!$date) continue;

            $monthKey = substr($date, 0, 7); // YYYY-MM
            $groups[$monthKey][] = $m;
        }

        // Trie les groupes par clé (déjà trié par date, mais on sécurise)
        ksort($groups);

        return $this->render('upcoming/index.html.twig', [
            'groups' => $groups,
            'img' => $tmdb,
        ]);
    }
}

