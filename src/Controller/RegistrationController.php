<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    // Route for the registration page
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Create a new User entity
        $user = new User();

        // Create a form based on the RegistrationFormType and bind it to the User entity
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Handle the request, process form data if available
        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the plain password from the form
            $plainPassword = $form->get('password')->getData();

            // Hash the plain password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            // Set the hashed password on the User entity
            $user->setPassword($hashedPassword);

            // Persist the new User entity to the database
            $entityManager->persist($user);
            $entityManager->flush(); // Save changes to the database

            // Redirect to the home page after successful registration
            return $this->redirectToRoute('home');
        }

        // Render the registration form view
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(), // Pass the form view to the template
        ]);
    }
}
