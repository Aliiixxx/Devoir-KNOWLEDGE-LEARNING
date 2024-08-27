<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Route for the login page
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Uncomment this block to redirect authenticated users
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Retrieve the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login page with the last entered username and error (if any)
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    // Route for the logout action
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be left blank as it is intercepted by the security component of Symfony
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
