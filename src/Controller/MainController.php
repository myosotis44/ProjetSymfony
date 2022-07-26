<?php

namespace App\Controller;

use App\Form\Model\OutFilterFormModel;
use App\Form\OutFilterFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
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
        public function home(): Response
        {
            return $this->redirectToRoute('out_index');
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
