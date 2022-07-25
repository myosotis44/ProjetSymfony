<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/user/{id}', name: 'app_user', requirements: ['id' => '\d+'])]
    public function displayProfil(int $id, ParticipantRepository $participantRepository): Response
    {
        $user = $participantRepository->find($id);
        return $this->render('user/index.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/user/{id}/update', name: 'user_update')]
    public function updateProfil(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,
                                 ParticipantRepository $participantRepository,
                                 UserPasswordHasherInterface $userPasswordHasher, int $id): Response
    {
        $user = $participantRepository->find($id);
        $user->setImageUtilisateur(
            new File($this->getParameter('images_directory').'/'.$user->getImageUtilisateur())
        );
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
}

