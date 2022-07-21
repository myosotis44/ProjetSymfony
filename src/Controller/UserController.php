<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\UserType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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
    public function updateProfil(Request $request, EntityManagerInterface $entityManager,
                                 ParticipantRepository $participantRepository, UserPasswordHasherInterface $userPasswordHasher, int $id): Response
    {
        $user = $participantRepository->find($id);
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()  && $userForm->get('password') === $userForm->get('confirmation') ){
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $userForm->get('password')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User successfully updated!');

            return $this->redirectToRoute('app_user', ['id' => $user->getId(),
                ]);
        }
        // affiche le formulaire
        return $this->render('user/update.html.twig', [
            'userForm' => $userForm->createView()
        ]);
    }



}
