<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil/{user}', name: 'app_profil')]
    public function index(User $user): Response
    {
        return $this->render('profil/index.html.twig', [
            'user' => $user,
        ]);
    }
}
