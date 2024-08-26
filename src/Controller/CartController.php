<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Achat;
use App\Entity\Lecon;
use App\Entity\Cursus;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $entityManager;
    private $stripeService;

    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService)
    {
        $this->entityManager = $entityManager;
        $this->stripeService = $stripeService;
    }

    #[Route('/cart', name: 'mon_panier')]
    public function afficherCart(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour accéder à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $cartItems = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);

        if (empty($cartItems)) {
            return $this->render('cart/index.html.twig', [
                'cartItems' => [], 
            ]);
        }

        $lineItems = [];
        foreach ($cartItems as $item) {
            if ($item->getLecon()) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item->getLecon()->getTitle(),
                        ],
                        'unit_amount' => $item->getLecon()->getPrice() * 100,
                    ],
                    'quantity' => 1,
                ];
            } elseif ($item->getCursus()) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item->getCursus()->getTitle(),
                        ],
                        'unit_amount' => $item->getCursus()->getPrice() * 100, 
                    ],
                    'quantity' => 1,
                ];
            }
        }

        $successUrl = $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        
        try {
            $session = $this->stripeService->createCheckoutSession($lineItems, $successUrl, $cancelUrl);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('mon_panier');
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
            'clientSecret' => isset($session) ? $session->id : null, 
        ]);
    }

    #[Route('/ajouter-lecon/{id}', name: 'ajouter_lecon')]
    public function ajouterLecon(int $id): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour ajouter des articles à votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $lecon = $this->entityManager->getRepository(Lecon::class)->find($id);
        if (!$lecon) {
            $this->addFlash('error', 'Leçon non trouvée.');
            return $this->redirectToRoute('home');
        }

        $existingCartItem = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user, 'lecon' => $lecon]);
        if ($existingCartItem) {
            $this->addFlash('error', 'Cette leçon est déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }

        $cartItem = new Cart();
        $cartItem->setUser($user);
        $cartItem->setLecon($lecon);

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();

        $this->addFlash('success', 'Leçon ajoutée au panier.');

        return $this->redirectToRoute('mon_panier');
    }

    #[Route('/ajouter-cursus/{id}', name: 'ajouter_cursus')]
public function ajouterCursus(int $id): RedirectResponse
{
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'Veuillez vous connecter pour ajouter des articles à votre panier.');
        return $this->redirectToRoute('app_login');
    }

    $cursus = $this->entityManager->getRepository(Cursus::class)->find($id);
    if (!$cursus) {
        $this->addFlash('error', 'Cursus non trouvé.');
        return $this->redirectToRoute('home');
    }

    $cartItems = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);
    foreach ($cartItems as $item) {
        if ($item->getLecon() && $item->getLecon()->getCursus() && $item->getLecon()->getCursus()->getId() === $cursus->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas ajouter ce cursus car une ou plusieurs leçons de ce cursus sont déjà dans votre panier.');
            return $this->redirectToRoute('mon_panier');
        }
    }

    $existingCartItem = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user, 'cursus' => $cursus]);
    if ($existingCartItem) {
        $this->addFlash('error', 'Ce cursus est déjà dans votre panier.');
        return $this->redirectToRoute('mon_panier');
    }

    $cartItem = new Cart();
    $cartItem->setUser($user);
    $cartItem->setCursus($cursus);

    $this->entityManager->persist($cartItem);
    $this->entityManager->flush();

    $this->addFlash('success', 'Cursus ajouté au panier.');

    return $this->redirectToRoute('mon_panier');
}


    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cartItems = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);

        foreach ($cartItems as $cartItem) {
            $achat = new Achat();
            $achat->setUser($user);
            if ($cartItem->getLecon()) {
                $achat->setLecon($cartItem->getLecon());
            } else {
                $achat->setCursus($cartItem->getCursus());
            }
            $achat->setCreatedAt(new \DateTime());

            $this->entityManager->persist($achat);
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Paiement réussi et vos achats ont été enregistrés !');

        return $this->redirectToRoute('mes_lecons');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->addFlash('error', 'Paiement annulé.');

        return $this->redirectToRoute('mon_panier');
    }

    #[Route('/supprimer-article/{id}', name: 'supprimer_article')]
    public function supprimerArticle($id): RedirectResponse
    {
        $cartItem = $this->entityManager->getRepository(Cart::class)->find($id);
        if ($cartItem) {
            $this->entityManager->remove($cartItem);
            $this->entityManager->flush();

            $this->addFlash('success', 'Article supprimé du panier.');
        } else {
            $this->addFlash('error', 'Article non trouvé.');
        }

        return $this->redirectToRoute('mon_panier');
    }
}
