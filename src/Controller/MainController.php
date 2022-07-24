<?php

namespace App\Controller;

use App\Form\Model\OutFilterFormModel;
use App\Form\OutFilterFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("", name="main_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function home(Request $request,SortieRepository $sortieRepository): Response
    {
        $filteredOuts = null;
        $filter = new OutFilterFormModel();
        $filterForm = $this->createForm(OutFilterFormType::class, $filter);

        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filteredOuts = $sortieRepository->outFilterDQLGenerator($filter);
        }

        return $this->render('out/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'filteredOuts' => $filteredOuts
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(Request $request,
                         EntityManagerInterface $manager): Response
    {
        return $this->render('main/index.html.twig' );
    }
}
