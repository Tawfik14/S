<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/movies/new', name: 'admin_movie_new')]
    public function newMovie(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $synopsis = (string) $request->request->get('synopsis', '');
            $videoUrl = (string) $request->request->get('video_url', '');
            $posterUrl = (string) $request->request->get('poster_url', '');
            $actorsCsv = (string) $request->request->get('actors', '');

            if ($title === '') {
                return $this->render('admin/new_movie.html.twig', [ 'error' => 'Le titre est requis' ]);
            }

            $movie = new Movie();
            $movie->setTitle($title);
            $movie->setSynopsis($synopsis ?: null);
            $movie->setVideoUrl($videoUrl ?: null);
            $movie->setPosterUrl($posterUrl ?: null);

            $names = array_filter(array_map('trim', explode(',', $actorsCsv)));
            foreach ($names as $name) {
                if ($name === '') { continue; }
                $actor = $em->getRepository(Actor::class)->findOneBy(['name' => $name]);
                if (!$actor) { $actor = (new Actor())->setName($name); }
                $movie->addActor($actor);
            }

            $em->persist($movie);
            $em->flush();

            return $this->redirectToRoute('movie_show', ['id' => $movie->getId()]);
        }

        return $this->render('admin/new_movie.html.twig');
    }
}