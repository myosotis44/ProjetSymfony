<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use League\Csv\Exception;
use League\Csv\Reader;
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
        $this->addVilles();
        $this->addLieu();
        //$this->addVillesCSV);
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
    public function addVilles() {
        $villesList = ['Rennes'=> 35000, 'Nantes' => 44000, 'Niort'=>79000, 'Quimper'=> 29000];
        foreach ($villesList as $villesNom=>$codePostal) {
            $villesObject = new Ville();
            $villesObject->setNom($villesNom);
            $villesObject->setCodePostal($codePostal);
            $this->manager->persist($villesObject);
        }
        $this->manager->flush();

    }
    public function addVillesCSV() {
        try {
            $reader = Reader::createFromPath("/../../data/csv/villes_france.csv");
            $reader->setHeaderOffset(0);
            $records =$reader->getRecords();
            dump($records);
            foreach ($records as $offset => $record) {
                foreach ($record as $villeValues) {
                    $villeInfo = explode(";",$villeValues);
                    $villeObject = new Ville();
                    $villeObject->setNom(ucwords(strtolower($villeInfo[1])));
                    $villeObject->setCodePostal($villeInfo[2]);
                    $this->manager->persist($villeObject);
                }
            }
            $this->manager->flush();
        }
        catch (Exception $exception) {
            dump($exception->getMessage());
        }
    }
    public function addLieu() {
        $villesList = $this->manager->getRepository(Ville::class)->findAll();
        $lieuList = ['Piscine','Salle des fêtes','Salle de Sport','Auberge Communale'];
        foreach ($villesList as $villeObject) {
            foreach ($lieuList as $lieuName) {
                $lieuObject = new Lieu();
                $lieuObject->setNom($lieuName . ' - ' . $villeObject->getNom());
                $lieuObject->setVille($villeObject);
                $lieuObject->setLatitude(0);
                $lieuObject->setLongitude(0);
                $lieuObject->setRue('Rue ' . $lieuName);
                $this->manager->persist($lieuObject);
            }
        }
        $this->manager->flush();
    }

    public function addEtat() {
        $etatList = ['En création','Ouverte','Clôturée','Activité en cours', 'Activité Terminée', 'Annulée'];
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
            $utilisateur->setPseudo($this->generator->userName);
            $utilisateur->setImageUtilisateur("something");

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
        $utilisateur->setPseudo("test");

        $password = $this->hasher->hashPassword($utilisateur, "testtest");
        $utilisateur->setPassword($password);
        $this->manager->persist($utilisateur);

        $this->manager->flush();
    }
}
