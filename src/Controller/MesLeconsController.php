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
    #[Route('/mes-lecons', name: 'mes_lecons')]
    public function index(EntityManagerInterface $entityManager, UserInterface $user): Response
    {

        $formations = $entityManager->getRepository(Formation::class)->findAll();
        $formationsCompletes = [];
        $lecons = []; 

        foreach ($user->getAchats() as $achat) {
            if ($achat->getLecon()) {
                $lecons[] = $achat->getLecon();
            } elseif ($achat->getCursus()) {
                foreach ($achat->getCursus()->getLecons() as $lecon) {
                    $lecons[] = $lecon;
                }
            }
        }

        foreach ($formations as $formation) {
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
                }

                $formationsCompletes[] = $formation;
            }
        }

        return $this->render('mes_lecons/index.html.twig', [
            'lecons' => $lecons,
            'formationsCompletes' => $formationsCompletes
        ]);
    }
}
