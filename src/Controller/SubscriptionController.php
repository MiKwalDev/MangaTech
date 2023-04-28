<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    private $entityManager;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, SubscriptionRepository $repo)
    {
        $this->entityManager = $entityManager;
        $this->repo = $repo;
    }

    #[Route('/admin/subscriptions', name: 'app_admin_subscriptions')]
    public function adminIndex(): Response
    {
        $subs = $this->repo->findAll();

        return $this->render('admin/subscription/index.html.twig', [
            'subs' => $subs,
        ]);
    }

    #[Route('/admin/subscription/new', name: 'app_admin_sub_new')]
    public function new(Request $request): Response
    {
        $sub = new Subscription();
        
        $form = $this->createForm(SubscriptionType::class, $sub);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($sub);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_subscriptions');

        }

        return $this->render('admin/subscription/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/subscription/edit/{sub}', name: 'app_admin_subscription_edit')]
    public function edit(Request $request, Subscription $sub): Response
    {
        $this->repo->find($sub);

        $form = $this->createForm(SubscriptionType::class, $sub);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($sub);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_subscriptions');

        }

        return $this->render('admin/subscription/edit.html.twig', [
            'form' => $form->createView(),
            'sub' => $sub,
        ]);
    }

    #[Route('/subscriptions', name: 'app_subscriptions')]
    public function index(): Response
    {
        $subs = $this->repo->findAll();

        return $this->render('client/subscription/index.html.twig', [
            'subs' => $subs,
        ]);
    }

    #[Route('/user/subscription', name: 'app_user_subscription')]
    public function subscribe(Request $request, UserRepository $userRepo)
    {
        $user = $userRepo->find($request->request->get('user_id'));
        $sub = $this->repo->find($request->request->get('sub_id'));
        
        $user->setSubscription($sub);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Vous êtes maintenant abonné.');

        return $this->redirectToRoute('app_home');
    }

}
