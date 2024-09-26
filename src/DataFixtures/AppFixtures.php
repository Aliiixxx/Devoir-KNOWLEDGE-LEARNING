<?php

namespace App\DataFixtures;

use App\Entity\Formation;
use App\Entity\Cursus;
use App\Entity\Lecon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $formationsData = [
            ['title' => 'Musique'],
            ['title' => 'Jardinage'],
            ['title' => 'Cuisine'],
            ['title' => 'Informatique']
        ];

        $formations = [];

        foreach ($formationsData as $data) {
            $formation = new Formation();
            $formation->setTitle($data['title']);
            $manager->persist($formation);
            $formations[] = $formation;
        }

        $cursusData = [
            ['title' => 'Cursus d\'initiation à la guitare', 'price' => 50, 'formation' => $formations[0]],
            ['title' => 'Cursus d\'initiation au piano', 'price' => 50, 'formation' => $formations[0]],
            ['title' => 'Cursus d\'initiation au développement web', 'price' => 60, 'formation' => $formations[3]],
            ['title' => 'Cursus d\'initiation au jardinage', 'price' => 30, 'formation' => $formations[1]],
            ['title' => 'Cursus d\'initiation à la cuisine', 'price' => 44, 'formation' => $formations[2]],
            ['title' => 'Cursus d\'initiation à l\'art du dressage culinaire', 'price' => 48, 'formation' => $formations[2]],
        ];

        $cursus = [];

        foreach ($cursusData as $data) {
            $cursusItem = new Cursus();
            $cursusItem->setTitle($data['title']);
            $cursusItem->setPrice($data['price']);
            $cursusItem->setFormation($data['formation']);
            $manager->persist($cursusItem);
            $cursus[] = $cursusItem;
        }

        $leconsData = [
            ['title' => 'Leçon n°1 : Découverte de l\'instrument', 'price' => 26, 'cursus' => $cursus[0]],
            ['title' => 'Leçon n°2 : Les accords et les gammes', 'price' => 26, 'cursus' => $cursus[0]],
            ['title' => 'Leçon n°1 : Découverte de l\'instrument', 'price' => 26, 'cursus' => $cursus[1]],
            ['title' => 'Leçon n°2 : Les accords et les gammes', 'price' => 26, 'cursus' => $cursus[1]],
            ['title' => 'Leçon n°1 : Les langages HTML et CSS', 'price' => 32, 'cursus' => $cursus[2]],
            ['title' => 'Leçon n°2 : Dynamiser votre site avec Javascript', 'price' => 32, 'cursus' => $cursus[2]],
            ['title' => 'Leçon n°1 : Les outils du jardinier', 'price' => 16, 'cursus' => $cursus[3]],
            ['title' => 'Leçon n°2 : Jardiner avec la lune', 'price' => 16, 'cursus' => $cursus[3]],
            ['title' => 'Leçon n°1 : Les modes de cuisson', 'price' => 23, 'cursus' => $cursus[4]],
            ['title' => 'Leçon n°2 : Les saveurs', 'price' => 23, 'cursus' => $cursus[4]],
            ['title' => 'Leçon n°1 : Mettre en œuvre le style dans l\'assiette', 'price' => 26, 'cursus' => $cursus[5]],
            ['title' => 'Leçon n°2 : Harmoniser un repas à quatre plats', 'price' => 26, 'cursus' => $cursus[5]],
        ];

        foreach ($leconsData as $data) {
            $lecon = new Lecon();
            $lecon->setTitle($data['title']);
            $lecon->setPrice($data['price']);
            $lecon->setCursus($data['cursus']);
            $manager->persist($lecon);
        }

        $manager->flush();
    }
}
