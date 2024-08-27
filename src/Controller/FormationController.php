<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Cursus;
use App\Entity\Lecon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FormationController extends AbstractController
{
    private $entityManager;

    // Constructor to initialize the EntityManager
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Route to display details of a specific cursus
    #[Route('/cursus/{id}', name: 'cursus_show')]
    public function showCursus(Cursus $cursus): Response
    {
        // Render the view for displaying a cursus
        return $this->render('formation/cursus_show.html.twig', [
            'cursus' => $cursus,
        ]);
    }

    // Route to display details of a specific lesson
    #[Route('/lecon/{id}', name: 'lecon_show')]
    public function showLecon(Lecon $lecon): Response
    {
        // Render the view for displaying a lesson
        return $this->render('formation/lecon_show.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    // Route to add a lesson to the user's cart
    #[Route('/cart/ajouter/lecon/{id}', name: 'ajouter_lecon')]
    public function ajouterLeconCart(Lecon $lecon): RedirectResponse
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            // Add flash message to notify the user to log in
            $this->addFlash('error', 'Veuillez vous connecter pour ajouter des leçons à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        // Check if the lesson is already in the user's cart
        $existingItem = $this->entityManager->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'lecon' => $lecon,
        ]);

        if ($existingItem) {
            // Add flash message if the lesson is already in the cart
            $this->addFlash('error', 'Cette leçon est déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }

        // Create a new cart item for the lesson
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setLecon($lecon);
        $cart->setCreatedAt(new \DateTime());

        // Save the cart item to the database
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        // Add flash message to notify the user of the successful addition
        $this->addFlash('success', 'Leçon ajoutée au panier.');
        return $this->redirectToRoute('mon_panier');
    }

    // Route to add a cursus to the user's cart
    #[Route('/cart/ajouter/cursus/{id}', name: 'ajouter_cursus')]
    public function ajouterCursusCart(Cursus $cursus): RedirectResponse
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            // Add flash message to notify the user to log in
            $this->addFlash('error', 'Veuillez vous connecter pour ajouter des cursus à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        // Check if the cursus is already in the user's cart
        $existingItem = $this->entityManager->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'cursus' => $cursus,
        ]);

        if ($existingItem) {
            // Add flash message if the cursus is already in the cart
            $this->addFlash('error', 'Ce cursus est déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }

        // Create a new cart item for the cursus
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCursus($cursus);
        $cart->setCreatedAt(new \DateTime());

        // Save the cart item to the database
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        // Add flash message to notify the user of the successful addition
        $this->addFlash('success', 'Cursus ajouté au panier.');
        return $this->redirectToRoute('mon_panier');
    }
}
