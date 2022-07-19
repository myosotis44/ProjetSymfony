<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        return $this->redirectToRoute('app_login');
    }
    /**
     * @Route("/test", name="test")
     */
    public function test(): Response
    {
        return $this->render('main/index.html.twig' , [
        ]);
    }
}
