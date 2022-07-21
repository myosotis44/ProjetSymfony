<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $generator;
    private ObjectManager $manager;
    private UserPasswordHasherInterface $hasher;

    public function load(ObjectManager $manager): void
    {
        // functions ADD
        $this->manager = $manager;
        $this->addCampus();
        $this->addUsers();
        $this->addEtat();
    }

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->generator = Factory::create("fr_FR");
        $this->hasher = $hasher;
    }

    public function addCampus() {
        $campusList = ["Rennes", "Nantes", "Niort", "Quimper"];
        foreach ($campusList as $campusName) {
            $campusObject = new Campus();
            $campusObject->setNom($campusName);
            $this->manager->persist($campusObject);
        }
        $this->manager->flush();
    }
    public function addEtat() {
        $etatList = ['Crée','Ouverte','Clôturée','Activité en cours', 'Passées', 'Annulée'];
        foreach ($etatList as $etatName ) {
            $etatObj = new Etat();
            $etatObj->setLibelle($etatName);
            $this->manager->persist($etatObj);
        }
        $this->manager->flush();
    }
    public function addUsers() {
        $campusList = $this->manager->getRepository(Campus::class)->findAll();
        for ($i=0 ; $i < 10; $i++) {
            $utilisateur = new Participant();
            $utilisateur->setRoles(["ROLE_USER"]);
            $utilisateur->setActif(true);
            $utilisateur->setCampus($this->generator->randomElement($campusList));
            $utilisateur->setMail($this->generator->email);
            $utilisateur->setNom($this->generator->lastName);
            $utilisateur->setPrenom($this->generator->firstName);
            $utilisateur->setTelephone($this->generator->phoneNumber);

            $password = $this->hasher->hashPassword($utilisateur, $this->generator->password);
            $utilisateur->setPassword($password);
            $this->manager->persist($utilisateur);
        }

        $utilisateur = new Participant();
        $utilisateur->setRoles(["ROLE_USER"]);
        $utilisateur->setActif(true);
        $utilisateur->setCampus($this->generator->randomElement($campusList));
        $utilisateur->setMail("test@test.com");
        $utilisateur->setNom($this->generator->lastName);
        $utilisateur->setPrenom($this->generator->firstName);
        $utilisateur->setTelephone($this->generator->phoneNumber);

        $password = $this->hasher->hashPassword($utilisateur, "testtest");
        $utilisateur->setPassword($password);
        $this->manager->persist($utilisateur);

        $this->manager->flush();
    }
}
