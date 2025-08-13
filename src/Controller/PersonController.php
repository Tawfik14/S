<?php

namespace App\Controller;

use App\Service\TmdbClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PersonController extends AbstractController
{
    #[Route('/person/{id}-{slug}', name: 'app_person_show', requirements: ['id' => '\d+'])]
    public function show(int $id, string $slug, TmdbClient $tmdb, SluggerInterface $slugger): Response
    {
        $p = $tmdb->person($id);
        $expected = strtolower($slugger->slug($p['name'] ?? 'personne'));
        if ($slug !== $expected) {
            return $this->redirectToRoute('app_person_show', ['id' => $id, 'slug' => $expected], 301);
        }

        $ext = $tmdb->personExternalIds($id);
        $credits = $tmdb->personCombinedCredits($id);

        // Séparer les crédits "movie" et "tv"
        $cast = array_filter($credits['cast'] ?? [], fn($c) => ($c['media_type'] ?? '') === 'movie');
        $crew = array_filter($credits['crew'] ?? [], fn($c) => ($c['media_type'] ?? '') === 'movie');

        // Trier par date de sortie (desc)
        $byDate = function($a, $b) {
            $da = $a['release_date'] ?? '0000-00-00';
            $db = $b['release_date'] ?? '0000-00-00';
            return strcmp($db, $da);
        };
        usort($cast, $byDate);
        usort($crew, $byDate);

        // Calcul de l'âge si possible
        $age = null;
        if (!empty($p['birthday'])) {
            $birth = new \DateTime($p['birthday']);
            $end = !empty($p['deathday']) ? new \DateTime($p['deathday']) : new \DateTime();
            $diff = $birth->diff($end);
            $age = $diff->y;
        }

        // “Connu pour” : top 10 par popularité
        $knownFor = $cast;
        usort($knownFor, fn($a,$b) => ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0));
        $knownFor = array_slice($knownFor, 0, 10);

        return $this->render('person/show.html.twig', [
            'p' => $p,
            'age' => $age,
            'ext' => $ext,
            'cast' => $cast,
            'crew' => $crew,
            'knownFor' => $knownFor,
            'img' => $tmdb,
        ]);
    }
}

