<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(): Response
    {
        $erreur = "'essai d'affichage de l'erreur";

        return $this->render('main/index.html.twig', [
            "error" => $erreur,
        ]);
//        return $this->render('main/index.html.twig', compact("erreur"));
    }
}
