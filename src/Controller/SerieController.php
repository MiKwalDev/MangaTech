<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Form\SerieType;
use App\Repository\SerieRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SerieController extends AbstractController
{
    private $entityManager;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, SerieRepository $repo)
    {
        $this->entityManager = $entityManager;
        $this->repo = $repo;
    }

    #[Route('/admin/series', name: 'app_admin_series')]
    public function adminIndex(): Response
    {
        $series = $this->repo->findAll();

        return $this->render('admin/serie/index.html.twig', [
            'series' => $series,
        ]);
    }

    #[Route('/admin/serie/new', name: 'app_admin_serie_new')]
    public function new(Request $request): Response
    {
        $serie = new Serie();
        
        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $serie->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));

            $this->entityManager->persist($serie);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_series');

        }

        return $this->render('admin/serie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/serie/edit/{category}', name: 'app_admin_serie_edit')]
    public function edit(Request $request, Serie $serie): Response
    {
        $this->repo->find($serie);

        $form = $this->createForm(SerieType::class, $serie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($serie);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_series');

        }

        return $this->render('admin/serie/edit.html.twig', [
            'form' => $form->createView(),
            'serie' => $serie,
        ]);
    }

    #[Route('/serie/{serie}', name: 'app_serie')]
    public function show(Serie $serie): Response
    {
        return $this->render('client/serie/show.html.twig', [
            'serie' => $serie,
        ]);
    }
}
