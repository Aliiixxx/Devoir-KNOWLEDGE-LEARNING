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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/cursus/{id}', name: 'cursus_show')]
    public function showCursus(Cursus $cursus): Response
    {
        return $this->render('formation/cursus_show.html.twig', [
            'cursus' => $cursus,
        ]);
    }

    #[Route('/lecon/{id}', name: 'lecon_show')]
    public function showLecon(Lecon $lecon): Response
    {
        return $this->render('formation/lecon_show.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    #[Route('/cart/ajouter/lecon/{id}', name: 'ajouter_lecon')]
    public function ajouterLeconCart(Lecon $lecon): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour ajouter des leçons à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $existingItem = $this->entityManager->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'lecon' => $lecon,
        ]);

        if ($existingItem) {
            $this->addFlash('error', 'Cette leçon est déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setLecon($lecon);
        $cart->setCreatedAt(new \DateTime());

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $this->addFlash('success', 'Leçon ajoutée au panier.');
        return $this->redirectToRoute('mon_panier');
    }

    #[Route('/cart/ajouter/cursus/{id}', name: 'ajouter_cursus')]
    public function ajouterCursusCart(Cursus $cursus): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour ajouter des cursus à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $existingItem = $this->entityManager->getRepository(Cart::class)->findOneBy([
            'user' => $user,
            'cursus' => $cursus,
        ]);

        if ($existingItem) {
            $this->addFlash('error', 'Ce cursus est déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCursus($cursus);
        $cart->setCreatedAt(new \DateTime());

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $this->addFlash('success', 'Cursus ajouté au panier.');
        return $this->redirectToRoute('mon_panier');
    }
}
