<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, TmdbClient $tmdb): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $results = $q !== '' ? $tmdb->searchMulti($q) : [];

        return $this->render('search/index.html.twig', [
            'q' => $q,
            'results' => $results,
            'img' => $tmdb,
        ]);
    }

    // Endpoint JSON consommé par l’auto-complétion du header
    #[Route('/api/search', name: 'app_search_api')]
    public function api(Request $request, TmdbClient $tmdb): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        if ($q === '') {
            return $this->json(['results' => []]);
        }

        $data = $tmdb->searchMulti($q);

        // On simplifie la réponse pour le front
        $out = [];
        foreach ($data as $r) {
            $title = $r['title'] ?? $r['name'] ?? null;
            if (!$title) continue;

            $out[] = [
                'id' => $r['id'] ?? null,
                'title' => $title,
                'year' => isset($r['release_date']) ? substr($r['release_date'], 0, 4)
                        : (isset($r['first_air_date']) ? substr($r['first_air_date'], 0, 4) : null),
                'poster' => $r['poster_path'] ?? null,
                'backdrop' => $r['backdrop_path'] ?? null,
                'media_type' => $r['media_type'] ?? null,
            ];
        }

        return $this->json(['results' => array_slice($out, 0, 10)]);
    }
}

