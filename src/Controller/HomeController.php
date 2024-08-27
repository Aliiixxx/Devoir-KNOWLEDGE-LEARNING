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
    // Route for the homepage
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all formations from the database
        $formations = $entityManager->getRepository(Formation::class)->findAll();

        // Get the currently logged-in user
        $user = $this->getUser();
        $cursusIdsInCart = [];
        $leconIdsInCart = [];

        // If a user is logged in, fetch the user's cart items
        if ($user) {
            $cartItems = $entityManager->getRepository(Cart::class)->findBy(['user' => $user]);

            // Iterate over the cart items and collect the IDs of cursus and lessons in the cart
            foreach ($cartItems as $item) {
                if ($item->getCursus()) {
                    $cursusIdsInCart[] = $item->getCursus()->getId(); // Collect cursus IDs
                }
                if ($item->getLecon()) {
                    $leconIdsInCart[] = $item->getLecon()->getId(); // Collect lesson IDs
                }
            }
        }

        // Render the homepage view and pass the formations, cursus IDs, and lesson IDs in the cart
        return $this->render('home/index.html.twig', [
            'formations' => $formations,
            'cursusIdsInCart' => $cursusIdsInCart,
            'leconIdsInCart' => $leconIdsInCart,
        ]);
    }
}
