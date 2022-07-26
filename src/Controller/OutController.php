<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\Model\OutFilterFormModel;
use App\Form\OutFilterFormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        $filteredOuts = null;
        $filter = new OutFilterFormModel();
        $filterForm = $this->createForm(OutFilterFormType::class, $filter);

        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filteredOuts = $sortieRepository->outFilterDQLGenerator($filter, $this->getUser());

            dump('Début de traitement : ', $filteredOuts);
            /*  "Afficher" => tous les états sauf "En création"
                "Modifier" & Publier => états "En création"
                "Annuler"
            */
            foreach ($filteredOuts as $eachFilteredOut) {

                $eachFilteredOut->addParticipant($this->getUser());

                $actions = array();

                if ($eachFilteredOut->getEtat()->getLibelle() === 'En création') {
                    $actions[] = 'Modifier';
                    $actions[] = 'Publier';
                }
                else {
                    $actions[] = 'Afficher';
                }

 /*               if ($eachFilteredOut->getEtat()->getLibelle() === 'Fermé' && $eachFilteredOut->getEtat()->getLibelle() === 'Fermé') {

                }*/

                $eachFilteredOut->setActions($actions);

            dump(implode('-',$actions));

            }

            dump($actions);
        }

        dump('Fin de traitement : ', $filteredOuts);

        return $this->render('out/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'filteredOuts' => $filteredOuts,
/*            'actions' => $actions*/
        ]);

    }


    #[Route('/create', name: 'create')]
    public function create(Request $request,
                            EntityManagerInterface $entityManager,
                            EtatRepository $etatRepository): Response {

        $sortie = new Sortie($this->getUser());
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $etatInitial = $etatRepository->findOneBy(['libelle'=>'Crée']);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setEtat($etatInitial);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée avec succès!');
            return $this->redirectToRoute('main_test');
        }
        return $this->render('out/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }


    /**
     * @Route("/{id}", name="detail")
     */
    public function detail(
                           SortieRepository $sortieRepository, int $id): Response {
        $sortieObject = $sortieRepository->find($id);

        if(!$sortieObject) {
            throw $this->createNotFoundException('Cette sortie n\'existe pas');
        }

        return $this->render('out/detail.html.twig', [
            'sortie' => $sortieObject,
        ]);
    }
}
