<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    /**
     * @Route("/lieu/rechercheAjaxByVille", name="lieu_rechercher_ajax_by_ville")
     */
    public function rechercheAjaxByVille(Request $request , EntityManagerInterface $entityManager, LieuRepository  $lieuRepository){
        $lieux = $lieuRepository->findBy(['ville' => $request->request->get('ville_id')]);
        $json_data = array();
        $i = 0;
        if(sizeof($lieux)> 0){
            foreach ($lieux as $lieu){
                $json_data[$i++] = array( 'id' => $lieu->getId(), 'nom' => $lieu->getNom());
            }
            return new JsonResponse($json_data);
        }else{
            $json_data[$i++] = array( 'id' => '', 'nom' => 'Pas de lieu correspondant à votre recherche.');
            return new JsonResponse($json_data);
        }
    }

}
