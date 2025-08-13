<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin/manage', name: 'app_admin_manage')]
    public function manage(): Response
    {
        return $this->render('admin/manage/index.html.twig');
    }

    // >>> AJOUT pour corriger l’erreur "app_admin_media" n’existe pas
    #[Route('/admin/media', name: 'app_admin_media')]
    public function media(): Response
    {
        // On réutilise la même vue que "manage" (à adapter si tu préfères une vue dédiée)
        return $this->render('admin/manage/index.html.twig');
    }
}

