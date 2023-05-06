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

    /**
     * Fonction permettant d'ajouter un lieu à une ville
     * Utilisée dans la fenetre modale (ajout lieu)
     * RDG UC 2014: En tant que participant je peux ajouter des lieux dans la plateforme.
     * @Route("/lieu/ajouterAjax", name="lieu_ajouter_ajax")
     */
    public function ajouterAjax(Request $request , EntityManagerInterface $entityManager, VilleRepository  $villeRepo){
        if($this->getUser() != null){
            $lieu = new Lieu();
            try{
                $ville = $villeRepo->find($request->query->get('ville_id'));
                $lieu->setNom($request->query->get('lieu_nom'));
                $lieu->setVille($ville);
                $lieu->setRue($request->query->get('lieu_rue'));
                $lieu->setLongitude($request->query->get('lieu_longitude'));
                $lieu->setLatitude($request->query->get('lieu_latitude'));
                $entityManager->persist($lieu);
                $entityManager->flush();
                return new Response('Ajout effectué.');
            }catch (\Exception $e){
                $content = json_encode(array('message' => 'You are not allowed to delete this post'));
                return new Response($content, 419);
            }
        }
        else{
            throw new AccessDeniedException('Que viens-tu voir par là!');
        }
    }
}
