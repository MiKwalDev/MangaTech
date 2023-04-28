<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BorrowingController extends AbstractController
{
    #[Route('/borrowing', name: 'app_borrowing')]
    public function index(): Response
    {
        return $this->render('borrowing/index.html.twig', [
            'controller_name' => 'BorrowingController',
        ]);
    }
}
