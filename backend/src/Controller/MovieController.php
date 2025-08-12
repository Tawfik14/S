<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EntityManagerInterface $em): Response
    {
        $movies = $em->getRepository(Movie::class)->findBy([], ['createdAt' => 'DESC']);
        return $this->render('movie/index.html.twig', [ 'movies' => $movies ]);
    }

    #[Route('/movies/{id}', name: 'movie_show')]
    public function show(Movie $movie): Response
    {
        return $this->render('movie/show.html.twig', [ 'movie' => $movie ]);
    }
}