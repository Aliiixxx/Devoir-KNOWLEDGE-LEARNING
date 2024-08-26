<?php

namespace App\Controller;

use App\Entity\Certification;
use App\Entity\Lecon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class LeconController extends AbstractController
{
    #[Route('/lecon/{id}', name: 'show_lecon')]
    public function showLecon(Lecon $lecon, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        return $this->render('formation/lecon_show.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    #[Route('/lecon/{id}/finish', name: 'finish_lecon')]
    public function finishLecon(Lecon $lecon, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        if (!$lecon->isRead()) {
            $lecon->setIsRead(true);
            $entityManager->persist($lecon);
            $entityManager->flush();
        }

        $formation = $lecon->getCursus()->getFormation();
        if ($formation->hasUserCompleted($user)) {
            $certificationExistante = $entityManager->getRepository(Certification::class)
                ->findOneBy(['user' => $user, 'formation' => $formation]);

            if (!$certificationExistante) {
                $certification = new Certification();
                $certification->setUser($user);
                $certification->setFormation($formation);
                $certification->setDateObtained(new \DateTime());
                $entityManager->persist($certification);
                $entityManager->flush();

                $this->addFlash('success', 'Félicitations! Vous avez obtenu une certification pour la formation: ' . $formation->getTitle());
            }
        }

        return $this->redirectToRoute('mes_lecons');
    }
}
