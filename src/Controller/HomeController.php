<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $formations = $entityManager->getRepository(Formation::class)->findAll();

        $user = $this->getUser();
        $cursusIdsInCart = [];
        $leconIdsInCart = [];

        if ($user) {
            $cartItems = $entityManager->getRepository(Cart::class)->findBy(['user' => $user]);

            foreach ($cartItems as $item) {
                if ($item->getCursus()) {
                    $cursusIdsInCart[] = $item->getCursus()->getId();
                }
                if ($item->getLecon()) {
                    $leconIdsInCart[] = $item->getLecon()->getId();
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'formations' => $formations,
            'cursusIdsInCart' => $cursusIdsInCart,
            'leconIdsInCart' => $leconIdsInCart,
        ]);
    }
}
