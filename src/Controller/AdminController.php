<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin/manage', name: 'app_admin_manage')]
    public function manage(): Response
    {
        return $this->render('admin/manage/index.html.twig');
    }

    #[Route('/admin/media', name: 'app_admin_media', methods: ['GET', 'POST'])]
    public function media(
        Request $request,
        EntityManagerInterface $em,
        MediaRepository $mediaRepository
    ): Response {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genresText = (string) $form->get('genresText')->getData();
            $genres = array_values(array_filter(array_map(
                fn(string $g) => trim($g),
                explode(',', $genresText)
            )));
            $media->setGenres($genres ?: null);

            $em->persist($media);
            $em->flush();

            $this->addFlash('success', 'Ajouté avec succès.');
            return $this->redirectToRoute('app_admin_media');
        }

        $list = $mediaRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/media/index.html.twig', [
            'form' => $form->createView(),
            'list' => $list,
        ]);
    }

    #[Route('/admin/quiz', name: 'app_admin_quiz')]
    public function quiz(): Response
    {
        return $this->render('admin/manage/quiz_placeholder.html.twig');
    }
}
