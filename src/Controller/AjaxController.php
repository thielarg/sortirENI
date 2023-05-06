<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * Fonction permettant de recuperer les villes
     * RDG UC 2014: En tant que participant je peux ajouter des lieux dans la plateforme.
     * Utilisée dans la fenetre modale (appel Ajax)
     * @Route("/ajax/ville_rechercher", name="ajax_ville_rechercher")
     */
    public function ville_rechercher(Request $request, VilleRepository $villeRepo){
        $recherche = $request->request->get('recherche');
        $villes = $villeRepo->findAjaxRecherche($recherche);
        if ($request->isXmlHttpRequest()) {
            $jsonData = array();
            $idx = 0;
            foreach($villes as $ville) {
                $temp = array(
                    'id' => $ville->getId(),
                    'nom' => $ville->getNom(),
                    'code_postal' => $ville->getCodePostal(),
                );
                $jsonData[$idx++] = $temp;
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }


    /**
     * @Route("/ajax/rechercheLieuByVille", name="rechercher_lieu_by_ville")
     */
    public function rechercheAjaxByVille(Request $request , EntityManagerInterface $entityManager, LieuRepository  $lieuRepository){
        //declaration des variables
        $json_data = array();
        $i = 0;
        //recherche les lieux correspondant à la ville selectionnée
        $lieux = $lieuRepository->findBy(['ville' => $request->request->get('ville_id')]);
        //si lieux trouvées ...
        if(sizeof($lieux)> 0){
            //pour chaque lieu, hydratation d'un tableau
            foreach ($lieux as $lieu){
                $json_data[$i++] = array( 'id' => $lieu->getId(), 'nom' => $lieu->getNom());
            }
            //renvoie un tableau au format json
            return new JsonResponse($json_data);
            //sinon (lieux non trouvé) ...
        }else{
            //hydratation du tableau avec : Pas de lieu correspondant à cette ville.
            $json_data[$i++] = array( 'id' => '', 'nom' => 'Pas de lieu correspondant à cette ville.');
            //renvoie un tableau au format json
            return new JsonResponse($json_data);
        }
    }
}
