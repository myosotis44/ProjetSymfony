<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\CreationLieuType;
use App\Form\Model\OutFilterFormModel;
use App\Form\OutFilterFormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Services\OutServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/out", name="out_")
 */
class OutController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index(Request $request, SortieRepository $sortieRepository, OutServices $outServices): Response
    {
        $filteredOuts = null;

        $filter = new OutFilterFormModel();
        $filterForm = $this->createForm(OutFilterFormType::class, $filter);

        $filterForm->handleRequest($request);
        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filteredOuts = $sortieRepository->outFilterDQLGenerator($filter, $this->getUser());
            $filteredOuts = $outServices->actionsFilter($filteredOuts, $this->getUser());
        }
        else {
            $filteredOuts = $sortieRepository->returnActive();
        }

        return $this->render('out/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'filteredOuts' => $filteredOuts
        ]);
    }


    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request,
                            EntityManagerInterface $entityManager,
                            EtatRepository $etatRepository,
                            LieuRepository $lieuRepository): Response {


        $lieu = new Lieu();
        $lieuForm = $this->createForm(CreationLieuType::class, $lieu);
        $lieuForm->handleRequest($request);
        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash('success', 'Lieu ajoutée avec succès!');
        }

        $sortie = new Sortie($this->getUser());
        $etatInitial = $etatRepository->findOneBy(['libelle'=>'En création']);
        $sortie->setEtat($etatInitial);
        $lieuInitial = $lieuRepository->findOneBy(['nom'=>'Piscine - Rennes']);
        $sortie->setLieu($lieuInitial);
        $sortie->setDateHeureDebut(new \DateTime());
        $sortie->setDateLimiteInscription(new \DateTime());
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            if($request->get('Publier') == "") {
                $etatPublier = $etatRepository->findOneBy(['libelle'=>'Ouverte']);
                $sortie = $sortie->setEtat($etatPublier);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée avec succès!');
            return $this->redirectToRoute('main_home');
        }

        return $this->render('out/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieuForm' => $lieuForm->createView()
        ]);
    }


    /**
     * @Route("/{id}", name="detail")
     */
    public function detail(SortieRepository $sortieRepository, int $id): Response {

        $sortieObject = $sortieRepository->find($id);

        if(!$sortieObject) {
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        return $this->render('out/detail.html.twig', [
            'sortie' => $sortieObject,
        ]);
    }


    /**
     * @Route("/{id}/subscribe", name="subscribe")
     */
    public function subscribe(EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie) {
            throw $this->createNotFoundException('Impossible de s\'inscrire car cette sortie n\'existe pas');
        }
        elseif ($sortie->getEtat()->getLibelle() != 'Ouverte') {
            throw $this->createNotFoundException('Impossible de s\'inscire à une sortie qui n\'est plus ouverte !!');
        }

        $sortie->addParticipant($this->getUser());

        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Clôturée']));
        };

        $sortieRepository->add($sortie, true);
        $this->addFlash('success', 'Félicitations ! Vous venez de vous inscrire à la sortie "' . $sortie->getNom()) . '"';

        return $this->redirectToRoute('out_index');
    }


    /**
     * @Route("/{id}/unsubscribe", name="unsubscribe")
     */
    public function unsubscribe(EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if(!$sortie) {
            throw $this->createNotFoundException('Impossible de se désinscrire car cette sortie n\'existe pas');
        }
        elseif ($sortie->getEtat()->getLibelle() != 'Ouverte') {
            throw $this->createNotFoundException('Impossible de se désinscire d\'une sortie qui n\'est plus ouverte !!');
        }

        $sortie->removeParticipant($this->getUser());

        if ($sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
        };

        $sortieRepository->add($sortie, true);
        $this->addFlash('success', 'Désolé de ne plus pouvoir compter sur votre présence lors de notre sortie "' . $sortie->getNom() . '" ...');

        return $this->redirectToRoute('out_index');
    }


    /**
     * @Route("/{id}/edit", name="edit")
     */
    public function edit(Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id) : Response {

        $sortie = $sortieRepository->find($id);

        if(!$sortie) {
            throw $this->createNotFoundException('Impossible de modifier une sortie qui n\'existe pas');
        }
        elseif ($sortie->getEtat()->getLibelle() != 'En création') {
            throw $this->createNotFoundException('Impossible de modifier une sortie qui n\'est plus en état de Création !!');
        }


        $sortieForm = $this->createForm(SortieType::class, $sortie)
            ->add('bntEnregistrer', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->add('bntPublier', SubmitType::class, [
                'label' => 'Publier la sortie'
            ])
            ->add('bntSupprimer', SubmitType::class, [
                'label' => 'Supprimer la sortie'
            ])
            ->add('btnAnnuler', SubmitType::class, [
                'label' => 'Annuler'
            ]);

        $sortieForm->handleRequest($request);

        if ($sortieForm->get('bntPublier')->isClicked()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
            $sortieRepository->add($sortie, true);
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" a été publiée avec succès !');
            return $this->redirectToRoute('out_detail', ['id' => $sortie->getId()]);
        }

        elseif ($sortieForm->get('bntSupprimer')->isClicked()) {
            $sortieRepository->remove($sortie, true);
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" a bien été supprimée !');
            return $this->redirectToRoute('out_index');
        }
        elseif ($sortieForm->get('btnAnnuler')->isClicked()) {
            return $this->redirectToRoute('out_index');
        }
        elseif ($sortieForm->get('bntEnregistrer')->isSubmitted() && $sortieForm->isValid()) {
            $sortieRepository->add($sortie, true);
            $this->addFlash('success', 'La sortie "' . $sortie->getNom() . '" a bien été modifée !');
            return $this->redirectToRoute('out_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('out/edit.html.twig', [
            'sortieForm' => $sortieForm->createView(),
        ]);
    }

}
