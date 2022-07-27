<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\CsvType;
use App\Form\UserType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="app_user", requirements={"id"="\d+"})
     */
    public function displayProfil(int $id, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->find($id);
        return $this->render('user/index.html.twig', [
            "user" => $user
        ]);
    }

    /**
     * @Route("/user/{id}/update", name="user_update")
     */
    public function updateProfil(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,
                                 ParticipantRepository $participantRepository,
                                 UserPasswordHasherInterface $userPasswordHasher, int $id): Response
    {
        $user = $participantRepository->find($id);

        // Crée une instance de File si une image utilisateur est enregistrée afin de pouvoir l'éditer ensuite
        if ($user->getImageUtilisateur() != null) {
            $user->setImageUtilisateur(
                new File($this->getParameter('images_directory').'/'.$user->getImageUtilisateur())
            );
        }

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        //$confirmPswd = $user->getPassword() === $userForm->get('confirmation')->getData();

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData()
                )
            );

           $eventImage = $userForm->get('imageFile')->getData();

            if ($eventImage) {
                $originalFilename = pathinfo($eventImage->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL

               $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $eventImage->guessExtension();

                    // Move the file to the directory where images are stored
                    try {
                        $eventImage->move(
                            $this->getParameter('images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        error_log($e->getMessage());
                    }

                // updates the 'eventImage' property to store the image file name
                // instead of its contents
            $user->setImageUtilisateur($newFilename);
            }

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'User successfully updated!');

                return $this->redirectToRoute('app_user', ['id' => $user->getId()]);
            }

        return $this->render('user/update.html.twig', [
            'userForm' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/user_register", name="user_register")
     */
    public function register_user(Request $request, EntityManagerInterface $entityManager,
                                  UserPasswordHasherInterface $userPasswordHasher) {

        $user = new Participant();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData()
                )
            );
            $user->setActif(true);
            $user->setRoles(["ROLE_USER"]);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé!');

            return $this->redirectToRoute('main_home');

        }
        return $this->render('user/create.html.twig', [
            'userForm' => $userForm->createView()
        ]);
    }

    /**
     * @Route("/admin/user_register_csv", name="user_register_csv")
     */
    public function register_user_csv(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $userPasswordHasher) {
        $user = new Participant();
        $userForm = $this->createForm(CsvType::class);
        $userForm->handleRequest($request);

        $fichierCsv = $userForm->get('fichier_csv')->getData();
        if ($userForm->isSubmitted()) {

            try {
                $fichierCsv->move(
                    $this->getParameter('csv_directory'),
                    $fichierCsv
                );
            } catch (FileException $e) {
                error_log($e->getMessage());
            }

            try {
                $reader = Reader::createFromPath(pathinfo($fichierCsv->getClientOriginalName(), PATHINFO_FILENAME));
                $reader->setHeaderOffset(0);
                $records =$reader->getRecords();
                dump($records);
                foreach ($records as $offset => $record) {
                    foreach ($record as $utilisateurValues) {
                        $utilisateurInfo = explode(";",$utilisateurValues);
                        $utilisateurObject = new Participant();
                        $utilisateurObject->setPseudo(ucwords(strtolower($utilisateurInfo[1])));
                        $utilisateurObject->setPrenom(ucwords(strtolower($utilisateurInfo[2])));
                        $utilisateurObject->setNom(ucwords(strtolower($utilisateurInfo[3])));
                        $utilisateurObject->setTelephone($utilisateurInfo[4]);
                        $utilisateurObject->setMail($utilisateurInfo[5]);
                        $utilisateurObject->setPassword($utilisateurInfo[6]);
                        $utilisateurObject->setCampus($utilisateurInfo[7]);
                        $utilisateurObject->setActif(true);
                        $utilisateurObject->setRoles(["ROLE_USER"]);

                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                                $user,
                                $userForm->get('password')->getData()
                            )
                        );
                        $this->$manager->persist($utilisateurObject);

                    }
                }
                $this->$manager->flush();
            }
            catch (Exception $exception) {
                dump($exception->getMessage());
            }

        }
        return $this->render('user/create_csv.html.twig', [
            'userForm' => $userForm->createView()
        ]);

    }

}

