<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\BorrowingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $entityManager;
    private $repo;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $repo)
    {
        $this->entityManager = $entityManager;
        $this->repo = $repo;
    }

    #[Route('/profil/{user}', name: 'app_profil')]
    public function index(User $user): Response
    {
        if ($user === $this->getUser()) {
            return $this->render('client/profil/index.html.twig', [
                'user' => $user,
            ]);
        } else {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à accéder à cette page!');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/user/subscription/cancel/{user}/{subscription}', name: 'app_user_subscription_cancel')]
    public function cancel(Request $request, User $user, Subscription $subscription, BorrowingRepository $bRepo)
    {
        if (
            $user === $this->getUser()
            && $request->getMethod() == "POST"
            && $subscription === $user->getSubscription()
            && count($bRepo->findByNotReturned($user)) === 0
        ) {
            $user->setSubscription(null);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre abonnement à été résilié.');
        } elseif (count($bRepo->findByNotReturned($user)) != 0) {
            $this->addFlash('error', 'Veuillez retourner vos emprunts avant de résilier.');
        } else {
            $this->addFlash('error', 'Impossible de résilier.');
        }

        

        return $this->redirectToRoute('app_profil', ["user" => $user->getId()]);
    }
}
