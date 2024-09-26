<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Formation;
use App\Entity\Cursus;
use App\Entity\Lecon;
use App\Entity\Achat;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    // Dependency injection for the entity manager to interact with the database.
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Route for the admin dashboard
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    // Route to manage users
    #[Route('/admin/users', name: 'admin_users')]
    public function manageUsers(): Response
    {
        // Retrieve all users from the database
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    // Route to edit a specific user
    #[Route('/admin/users/edit/{id}', name: 'admin_edit_user')]
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Update user details
            $user->setFirstname($request->request->get('firstname'));
            $user->setLastname($request->request->get('lastname'));
            $user->setEmail($request->request->get('email'));

            // Retrieve roles from the form using 'all' to get an array
            $roles = $request->request->all('roles'); 
            $user->setRoles($roles);
            
            // Update password if provided
            if ($request->request->get('password')) {
                $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
                $user->setPassword($hashedPassword);
            }
            
            // Persist changes to the database
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'user' => $user,
        ]);
    }


    // Route to delete a user
    #[Route('/admin/users/delete/{id}', name: 'admin_delete_user')]
    public function deleteUser(User $user): Response
{
    $achats = $this->entityManager->getRepository(Achat::class)->findBy(['user' => $user]);

    foreach ($achats as $achat) {
        $this->entityManager->remove($achat);
    }

    $this->entityManager->remove($user);
    $this->entityManager->flush();

    return $this->redirectToRoute('admin_users');
}
    // Route to remove a certification from a user
    #[Route('/admin/users/remove-certification/{id}', name: 'admin_remove_certification')]
    public function removeCertification(int $id): Response
    {
        $certification = $this->entityManager->getRepository(Certification::class)->find($id);

        if ($certification) {
            $this->entityManager->remove($certification);
            $this->entityManager->flush();
            $this->addFlash('success', 'Certification successfully removed.');
        } else {
            $this->addFlash('error', 'Certification not found.');
        }

        return $this->redirectToRoute('admin_users');
    }

    // Route to add a new user
    #[Route('/admin/users/add', name: 'admin_add_user')]
    public function addUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Set user details
            $user->setFirstname($request->request->get('firstname'));
            $user->setLastname($request->request->get('lastname'));
            $user->setEmail($request->request->get('email'));
            $user->setRoles([$request->request->get('roles')]);

            // Hash and set the password
            if ($request->request->get('password')) {
                $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/add_user.html.twig', [
            'user' => $user,
        ]);
    }

    // Route to manage formations
    #[Route('/admin/formations', name: 'admin_formations')]
    public function manageFormations(): Response
    {
        // Retrieve all formations, cursus, and lessons from the database
        $formations = $this->entityManager->getRepository(Formation::class)->findAll();
        $cursus = $this->entityManager->getRepository(Cursus::class)->findAll();
        $lecons = $this->entityManager->getRepository(Lecon::class)->findAll();

        return $this->render('admin/formations.html.twig', [
            'formations' => $formations,
            'cursus' => $cursus,
            'lecons' => $lecons,
        ]);
    }

    // Route to add a new formation
    #[Route('/admin/formations/add', name: 'admin_add_formation')]
    public function addFormation(Request $request): Response
    {
        $formation = new Formation();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $formation->setTitle($request->request->get('title'));

            $this->entityManager->persist($formation);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/add_formation.html.twig');
    }

    // Route to edit a specific formation
    #[Route('/admin/formations/edit/{id}', name: 'admin_edit_formation')]
    public function editFormation(Formation $formation, Request $request): Response
    {
        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $formation->setTitle($request->request->get('title'));

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/edit_formation.html.twig', [
            'formation' => $formation,
        ]);
    }

    // Route to delete a formation
    #[Route('/admin/formations/delete/{id}', name: 'admin_delete_formation')]
    public function deleteFormation(Formation $formation): Response
    {
        $this->entityManager->remove($formation);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_formations');
    }

    // Route to add a new cursus
    #[Route('/admin/cursus/add', name: 'admin_add_cursus')]
    public function addCursus(Request $request): Response
    {
        $formations = $this->entityManager->getRepository(Formation::class)->findAll();
        $cursus = new Cursus();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $cursus->setTitle($request->request->get('title'));
            $cursus->setPrice($request->request->get('price'));
            $formation = $this->entityManager->getRepository(Formation::class)->find($request->request->get('formation_id'));
            $cursus->setFormation($formation);

            $this->entityManager->persist($cursus);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/add_cursus.html.twig', [
            'formations' => $formations,
        ]);
    }

    // Route to edit a specific cursus
    #[Route('/admin/cursus/edit/{id}', name: 'admin_edit_cursus')]
    public function editCursus(Cursus $cursus, Request $request): Response
    {
        $formations = $this->entityManager->getRepository(Formation::class)->findAll();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $cursus->setTitle($request->request->get('title'));
            $cursus->setPrice($request->request->get('price'));
            $formation = $this->entityManager->getRepository(Formation::class)->find($request->request->get('formation_id'));
            $cursus->setFormation($formation);

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/edit_cursus.html.twig', [
            'cursus' => $cursus,
            'formations' => $formations,
        ]);
    }

    // Route to delete a cursus
    #[Route('/admin/cursus/delete/{id}', name: 'admin_delete_cursus')]
    public function deleteCursus(Cursus $cursus): Response
    {
        $this->entityManager->remove($cursus);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_formations');
    }

    // Route to add a new lesson
    #[Route('/admin/lecon/add', name: 'admin_add_lecon')]
    public function addLecon(Request $request): Response
    {
        $cursus = $this->entityManager->getRepository(Cursus::class)->findAll();
        $lecon = new Lecon();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $lecon->setTitle($request->request->get('title'));
            $lecon->setPrice($request->request->get('price'));
            $lecon->setContent($request->request->get('content'));
            $lecon->setVideoUrl($request->request->get('videoUrl'));
            $selectedCursus = $this->entityManager->getRepository(Cursus::class)->find($request->request->get('cursus_id'));
            $lecon->setCursus($selectedCursus);

            $this->entityManager->persist($lecon);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/add_lecon.html.twig', [
            'cursus' => $cursus,
        ]);
    }

    // Route to edit a specific lesson
    #[Route('/admin/lecon/edit/{id}', name: 'admin_edit_lecon')]
    public function editLecon(Lecon $lecon, Request $request): Response
    {
        $cursus = $this->entityManager->getRepository(Cursus::class)->findAll();

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            $lecon->setTitle($request->request->get('title'));
            $lecon->setPrice($request->request->get('price'));
            $lecon->setContent($request->request->get('content'));
            $lecon->setVideoUrl($request->request->get('videoUrl'));
            $selectedCursus = $this->entityManager->getRepository(Cursus::class)->find($request->request->get('cursus_id'));
            $lecon->setCursus($selectedCursus);

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/edit_lecon.html.twig', [
            'lecon' => $lecon,
            'cursus' => $cursus,
        ]);
    }

    // Route to delete a lesson
    #[Route('/admin/lecon/delete/{id}', name: 'admin_delete_lecon')]
    public function deleteLecon(Lecon $lecon): Response
    {
        $this->entityManager->remove($lecon);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_formations');
    }

    // Route to manage purchases
    #[Route('/admin/achats', name: 'admin_achats')]
    public function manageAchats(): Response
    {
        // Retrieve all purchases from the database
        $achats = $this->entityManager->getRepository(Achat::class)->findAll();

        return $this->render('admin/achats.html.twig', [
            'achats' => $achats,
        ]);
    }

    // Route to delete a purchase
    #[Route('/admin/achats/delete/{id}', name: 'admin_delete_achat')]
    public function deleteAchat(Achat $achat): Response
    {
        $this->entityManager->remove($achat);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_achats');
    }
}
