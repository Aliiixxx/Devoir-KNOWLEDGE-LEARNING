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
    // Route to display a lesson
    #[Route('/lecon/{id}', name: 'show_lecon')]
    public function showLecon(Lecon $lecon, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        // Render the lesson view with the lesson data
        return $this->render('formation/lecon_show.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    // Route to mark a lesson as finished
    #[Route('/lecon/{id}/finish', name: 'finish_lecon')]
    public function finishLecon(Lecon $lecon, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        // Check if the lesson is not yet marked as read
        if (!$lecon->isRead()) {
            $lecon->setIsRead(true); // Mark the lesson as read
            $entityManager->persist($lecon); // Persist the change
            $entityManager->flush(); // Flush to save changes in the database
        }

        // Get the formation associated with the lesson's cursus
        $formation = $lecon->getCursus()->getFormation();
        
        // Check if the user has completed all lessons in the formation
        if ($formation->hasUserCompleted($user)) {
            // Check if the user already has a certification for this formation
            $certificationExistante = $entityManager->getRepository(Certification::class)
                ->findOneBy(['user' => $user, 'formation' => $formation]);

            // If the user does not have a certification, create one
            if (!$certificationExistante) {
                $certification = new Certification();
                $certification->setUser($user);
                $certification->setFormation($formation);
                $certification->setDateObtained(new \DateTime());
                $entityManager->persist($certification);
                $entityManager->flush();

                // Add a success message to the session
                $this->addFlash('success', 'Congratulations! You have earned a certification for the formation: ' . $formation->getTitle());
            }
        }

        // Redirect the user to their lessons page
        return $this->redirectToRoute('mes_lecons');
    }
}
