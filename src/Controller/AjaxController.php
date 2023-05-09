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

/**
 * @Route("/ajax", name="ajax_")
 */
class AjaxController extends AbstractController
{
    /**
     * Fonction permettant de recuperer les villes
     * RDG UC 2014: En tant que participant je peux ajouter des lieux dans la plateforme.
     * Utilisée dans la fenetre modale (appel Ajax)
     * @Route("/rechercherVille", name="rechercherVille")
     */
    public function rechercherVille(Request $request, VilleRepository $villeRepo){
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
     * Fonction permettant de rechercher les lieux en fonction de la ville selectionnée
     * Cette fonction est utilisée dans fonction ajax chargerListeLieux()
     * RDG UC 2002: En tant qu'organisateur d'une sortie, je peux créer une nouvelle sortie (définir un lieu (nom, adresse, gps),
     * ...)
     * la requete SQL est :
     *      SELECT * FROM villes WHERE id = ?;
     * @Route("/rechercheLieuByVille", name="rechercher_lieu_by_ville")
     */
    public function rechercheLieuByVille(Request $request , LieuRepository  $lieuRepository){
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

    /**
     * Fonction permettant d'ajouter un lieu à une ville
     * Utilisée dans la fenetre modale (ajout lieu)
     * RDG UC 2014: En tant que participant je peux ajouter des lieux dans la plateforme.
     * @Route("/ajouterLieu", name="ajouterLieu")
     */
    public function ajouterLieu(Request $request , EntityManagerInterface $entityManager, VilleRepository  $villeRepo){
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
