<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
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
        $this->addSortie();
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
        $etatList = ['En création','Ouverte','Clôturée','Activité en cours', 'Activité Terminée', 'Annulée', 'Activité Historisée'];
        foreach ($etatList as $etatName ) {
            $etatObj = new Etat();
            $etatObj->setLibelle($etatName);
            $this->manager->persist($etatObj);
        }
        $this->manager->flush();
    }

    public function addSortie() {
        // etat id , nom
        // all in Rennes
        $villeRennes = $this->manager->getRepository(Ville::class)->findOneBy(['nom'=>'Rennes']);
        $campusRennes = $this->manager->getRepository(Campus::class)->findOneBy(['nom'=>'Rennes']);
        $userTest = $this->manager->getRepository(Participant::class)->findOneBy(['mail'=>'test@test.com']);
        $userList = $this->manager->getRepository(Participant::class)->findAll();

        $sortie[0] =  ["Activité en cours","Philo","2022-07-29 10:00:00",360,"2022-08-29 00:00:00",8, 'Infos sortie \"Philo\"','Piscine - Rennes',];
        $sortie[1] =  ["Clôturée","Origamie","2022-07-31 20:00:00",120, '2022-08-28 20:00:00',5, 'Infos sortie Origamie','Salle des Fêtes - Rennes'];
        $sortie[2] =  ["Clôturée","Perles","2022-07-31 20:00:00",60, '2022-08-28 00:00:00',12, 'Infos sortie \"Perles\"',"Salle de Sport - Rennes"];
        $sortie[3] =  ["Ouverte","Concert metal","2022-08-06 20:30:00",90, '2022-08-30 00:00:00',10, 'Infos sortie \"Concert metal\"','Salle des Fêtes - Rennes'];
        $sortie[4] =  ["Ouverte","Jardinage","2022-08-11 18:30:00",180, '2022-09-08 00:00:00',5, 'Infos sortie \"Jardinage\"',"Piscine - Rennes"];
        $sortie[5] =  ["En création","Cinéma","2022-08-13 21:00:00",90, '2022-09-13 00:00:00',10, 'Infos sortie \"Cinéma\"',"Auberge Communale - Rennes"];
        $sortie[6] =  ["Ouverte","Pate a sel","2022-08-12 19:30:00",30, '2022-09-06 00:00:00',5, 'Infos sortie \"Pate a sel\"', "Auberge Communale - Rennes"];

        for ($i=0 ; $i < 7; $i++) {
            if ($i <= 4 ) {
                $sortieObject = new Sortie($this->generator->randomElement($userList));
            }
            else {
                $sortieObject = new Sortie($userTest);
            }
            $sortieObject->setEtat($this->manager->getRepository(Etat::class)->findOneBy(['libelle'=>$sortie[$i][0]]));
            $sortieObject->setCampus($campusRennes);
            $sortieObject->setNom($sortie[$i][1]);
            try {
                $sortieObject->setDateHeureDebut(new \DateTime($sortie[$i][2]));
                $sortieObject->setDateLimiteInscription(new \DateTime($sortie[$i][4]));
            } catch (\Exception $e) {
                dump($e);
            }
            $sortieObject->setDuree($sortie[$i][3]);
            $sortieObject->setNbInscriptionsMax($sortie[$i][5]);
            $sortieObject->setInfosSortie($sortie[$i][6]);
            $sortieObject->setLieu($lieuList = $this->manager->getRepository(Lieu::class)->findOneBy(['nom' => $sortie[0][7] ]));
            $this->manager->persist($sortieObject);
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
            $utilisateur->setImageUtilisateur("imagepardefaut.jpg");

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

        $admin = new Participant();
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setActif(true);
        $admin->setCampus($this->generator->randomElement($campusList));
        $admin->setMail("admin@sortir.com");
        $admin->setNom($this->generator->lastName);
        $admin->setPrenom($this->generator->firstName);
        $admin->setTelephone($this->generator->phoneNumber);
        $admin->setPseudo("admin1");

        $passwordAdmin = $this->hasher->hashPassword($admin, "admin");
        $admin->setPassword($passwordAdmin);
        $this->manager->persist($admin);

        $this->manager->flush();
    }
}
