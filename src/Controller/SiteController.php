<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/site", name="site_")
 */
 class SiteController extends AbstractController
{
    /**
     * Fonction permettant d'afficher la page gestion des sites
     * @Route("", name="liste")
     */
    public function liste()
    {
        return $this->render('site/index.html.twig');
    }

    /**
     * Fonction permettant de rechercher un site via le champ de saisie dans la page gestion des sites
     * cette fonction est appelée par la fonction JS rechercherSite()
     * @Route("/recherche", name="_rechercher")
     */
    public function rechercher(Request $request, EntityManagerInterface $entityManager){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');

        $recherche = $request->request->get('recherche');
        $sites = $entityManager->getRepository(Site::class)->findAjaxRecherche($recherche);
        if ($request->isXmlHttpRequest()) {
            $jsonData = array();
            $idx = 0;
            foreach($sites as $site) {
                $temp = array(
                    'id' => $site->getId(),
                    'nom' => $site->getNom(),
                );
                $jsonData[$idx++] = $temp;
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * Fonction permettant de modifier le nom d'un site
     * cette fonction est appelée par la fonction JS save_row()
     * @Route("/modifier", name="modifier")
     */
    public function modifier(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $id = $request->request->get('id');
        $site = $entityManager->getRepository(Site::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $site->setNom($request->request->get('nom_site'));
            $entityManager->persist($site);
            $entityManager->flush();
            return new JsonResponse('site modifiée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * Fonction permettant de supprimer un site
     * cette fonction est appelée par la fonction JS suppr_row()
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimer(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $id = $request->request->get('id');
        $site = $entityManager->getRepository(Site::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $entityManager->remove($site);
            $entityManager->flush();
            return new JsonResponse('site supprimée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * Fonction permettant d'ajouter un site
     * cette fonction est appelée par la fonction JS sur clic bouton ajouter
     * @Route("/ajouter", name="ajouter")
     */
    public function ajouter(Request $request, EntityManagerInterface $entityManager){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_ADMIN, il verra la page 403 (accès refusé)
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $site = new Site();
        if ($request->isXmlHttpRequest()) {
            if($entityManager->getRepository(Site::class)->findBy(['nom' => $request->request->get('nom_site')]) == null){
                $site->setNom($request->request->get('nom_site'));
                $entityManager->persist($site);
                $entityManager->flush();
                return new JsonResponse('Site ajouté avec succès.');
            }else{
                return new JsonResponse(array('message' => 'site déjà existante.'), 419);
            }
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }
}
