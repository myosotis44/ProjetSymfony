<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\Model\OutFilterFormModel;
use App\Form\OutFilterFormType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Services\OutServices;
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
    public function index(Request $request, SortieRepository $sortieRepository, OutServices $outServices): Response
    {
        $filteredOuts = null;

        $filter = new OutFilterFormModel();
        $filterForm = $this->createForm(OutFilterFormType::class, $filter);

        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filteredOuts = $sortieRepository->outFilterDQLGenerator($filter, $this->getUser());
            $outServices->actionsFilter($filteredOuts, $this->getUser());
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
                            EtatRepository $etatRepository): Response {

        $sortie = new Sortie($this->getUser());
        $etatInitial = $etatRepository->findOneBy(['libelle'=>'En création']);
        $sortie->setEtat($etatInitial);
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
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
     * @Route("/subscribe/{id}", name="subscribe")
     */
    public function subscribe(SortieRepository $sortieRepository, int $id): Response
    {
        $result = $sortieRepository->find($id);

        if(!$result) {
            throw $this->createNotFoundException('Impossible de s\'inscrire car cette sortie n\'existe pas');
        }
        else {
            $result->addParticipant($this->getUser());
            $sortieRepository->add($result, true);
        }

        return $this->redirectToRoute('out_index');
    }


    /**
     * @Route("/unsubscribe/{id}", name="unsubscribe")
     */
    public function unsubscribe(SortieRepository $sortieRepository, int $id): Response
    {
        $result = $sortieRepository->find($id);

        if(!$result) {
            throw $this->createNotFoundException('Impossible de se désinscrire car cette sortie n\'existe pas');
        }
        else {
            $result->removeParticipant($this->getUser());
            $sortieRepository->add($result, true);
        }

        return $this->redirectToRoute('out_index');
    }
}

