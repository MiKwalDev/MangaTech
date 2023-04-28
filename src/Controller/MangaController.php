<?php

namespace App\Controller;

use App\Entity\Borrowing;
use App\Entity\Manga;
use App\Entity\Serie;
use App\Form\BorrowingType;
use App\Form\MangaType;
use App\Repository\BorrowingRepository;
use App\Repository\MangaRepository;
use App\Services\IsGranted;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MangaController extends AbstractController
{
    private $entityManager;
    private $repo;
    private $borrowingRepo;

    public function __construct(EntityManagerInterface $entityManager, MangaRepository $repo, BorrowingRepository $borrowingRepo)
    {
        $this->entityManager = $entityManager;
        $this->repo = $repo;
        $this->borrowingRepo = $borrowingRepo;
    }

    #[Route('/admin/serie/{serie}/mangas', name: 'app_admin_serie_mangas')]
    public function adminIndex(Serie $serie): Response
    {
        $mangas = $this->repo->findBySerie($serie);

        return $this->render('admin/manga/index.html.twig', [
            'mangas' => $mangas,
            'serie' => $serie,
        ]);
    }

    #[Route('/admin/serie/{serie}/manga/new', name: 'app_admin_serie_manga_new')]
    public function new(Request $request, Serie $serie): Response
    {
        $manga = new Manga();
        
        $form = $this->createForm(MangaType::class, $manga);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')['file']->getData();
            $extension = $file->guessExtension();

            if (!$extension) {
                // extension cannot be guessed
                $extension = 'bin';
            }
            
            $imageName = str_replace(' ', '-', strtolower("{$serie->getName()}_tome_{$request->get('manga')['volume_number']}"));

            $file->move(
                $this->getParameter('images_directory'),
                "{$imageName}.{$extension}"
            );

            $manga->getImage()->setUrl("/assets/images/{$imageName}.{$extension}");
            $manga->getImage()->setName($imageName);
            $manga->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $manga->setSerie($serie);

            $this->entityManager->persist($manga);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_series');

        }

        return $this->render('admin/manga/new.html.twig', [
            'form' => $form->createView(),
            'serie' => $serie,
        ]);
    }

    #[Route('/admin/serie/{serie}/manga/edit/{manga}', name: 'app_admin_manga_edit')]
    public function edit(Request $request, Manga $manga, Serie $serie): Response
    {
        $this->repo->find($manga);

        $form = $this->createForm(MangaType::class, $manga);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')['file']->getData();
            $extension = $file->guessExtension();

            if (!$extension) {
                // extension cannot be guessed
                $extension = 'bin';
            }

            $imageName = str_replace(' ', '-', strtolower("{$serie->getName()}_tome_{$request->get('manga')['volume_number']}"));

            $file->move(
                $this->getParameter('images_directory'),
                "{$imageName}.{$extension}"
            );

            $manga->getImage()->setUrl("/assets/images/{$imageName}.{$extension}");
            $manga->getImage()->setName($imageName);
            $manga->setCreatedAt(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
            $manga->setSerie($serie);

            $this->entityManager->persist($manga);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_serie_mangas', ['serie' => $serie->getId()]);

        }

        return $this->render('admin/manga/edit.html.twig', [
            "form" => $form->createView(),
            "manga" => $manga,
            "serie" => $serie,
        ]);
    }

    #[Route('/admin/mangas', name: 'app_admin_mangas')]
    public function adminList(): Response
    {
        $mangas = $this->repo->findAllOrderBy('created_at', 'DESC');

        return $this->render('admin/manga/list.html.twig', [
            'mangas' => $mangas,
        ]);
    }

    #[Route('/manga/{manga}', name: 'app_manga')]
    public function show(Manga $manga, Request $request): Response
    {
        $borrowing = new Borrowing();

        $form = $this->createForm(BorrowingType::class, $borrowing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $verificator = new IsGranted();
            $isGranted = $verificator->verifySubscription($this->getUser(), $manga, $this->borrowingRepo);
            
            if ($isGranted) {

                $borrowing->setBorrowingDate(new \DateTimeImmutable('now'), new DateTimeZone('Europe/Paris'));
                $borrowing->setIsReturned(false);
                $borrowing->setUser($this->getUser());
                $borrowing->setManga($manga);
    
                $manga->getStock()->setQuantity(($manga->getStock()->getQuantity()) - 1);
    
                $this->entityManager->persist($borrowing);
                $this->entityManager->persist($manga);
                $this->entityManager->flush();

                $this->addFlash('success', 'Votre emprunt à bien été enregistré.');
    
                return $this->redirectToRoute('app_manga', ['manga' => $manga->getId()]);

            } else {

                $this->addFlash('error', 'Vous ne pouvez pas emprunter ce livre.');

                return $this->redirectToRoute('app_manga', ['manga' => $manga->getId()]);
                
            }

        }

        return $this->render('client/manga/show.html.twig', [
            'manga' => $manga,
            'form' => $form->createView(),
        ]);
    }
}
