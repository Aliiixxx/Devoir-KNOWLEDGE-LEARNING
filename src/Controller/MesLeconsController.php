<?php

namespace App\Controller;

use App\Entity\Certification;
use App\Entity\Formation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MesLeconsController extends AbstractController
{
    // Define the route for the "Mes Leçons" page
    #[Route('/mes-lecons', name: 'mes_lecons')]
    public function index(EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        // Retrieve all formations from the database
        $formations = $entityManager->getRepository(Formation::class)->findAll();
        $formationsCompletes = []; // Array to hold completed formations
        $lecons = []; // Array to hold all the lessons purchased by the user

        // Iterate over the user's purchases to get all lessons and courses they have bought
        foreach ($user->getAchats() as $achat) {
            if ($achat->getLecon()) { // Check if the purchase is a single lesson
                $lecons[] = $achat->getLecon(); // Add lesson to the array
            } elseif ($achat->getCursus()) { // Check if the purchase is a cursus
                // Add all lessons from the cursus to the array
                foreach ($achat->getCursus()->getLecons() as $lecon) {
                    $lecons[] = $lecon;
                }
            }
        }

        // Check for each formation if the user has completed all its lessons
        foreach ($formations as $formation) {
            if ($formation->hasUserCompleted($user)) { // Method to check if user completed formation
                // Check if the user already has a certification for this formation
                $certificationExistante = $entityManager->getRepository(Certification::class)
                    ->findOneBy(['user' => $user, 'formation' => $formation]);

                // If no certification exists, create a new one
                if (!$certificationExistante) {
                    $certification = new Certification();
                    $certification->setUser($user);
                    $certification->setFormation($formation);
                    $certification->setDateObtained(new \DateTime()); // Set the current date as obtained date
                    $entityManager->persist($certification); // Persist the new certification
                    $entityManager->flush(); // Save changes to the database
                }

                $formationsCompletes[] = $formation; // Add completed formation to the array
            }
        }

        // Render the view with the user's lessons and completed formations
        return $this->render('mes_lecons/index.html.twig', [
            'lecons' => $lecons, // Pass lessons to the view
            'formationsCompletes' => $formationsCompletes // Pass completed formations to the view
        ]);
    }
}
