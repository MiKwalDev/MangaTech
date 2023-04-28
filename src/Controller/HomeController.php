<?php

namespace App\Controller;

use App\Repository\SerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SerieRepository $serieRepo): Response
    {
        $series = $serieRepo->findAll();

        return $this->render('client/home/index.html.twig', [
            "series" => $series,
        ]);
    }
}
