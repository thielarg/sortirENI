<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie", name="sortie_")
 */
class SortieController extends AbstractController
{
    /**
     * Fonction permettant de d'afficher les sorties repondant aux critères de filtre
     * RDG UC 2001: En tant que participant, je peux lister les sorties publiées sur chaque site, celles auxquelles
     * je suis inscrit et celles dont je suis l’organisateur. Je peux filtrer cette liste suivant différents critères
     * (voir maquette écran)
     * @Route("/liste", name="liste")
     */
    public function liste(Request $request, SiteRepository $siteRepo, EtatRepository $etatRepo, SortieRepository $sortieRepo)
    {
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');

        //appel de la methode rechercheDetaillee dans SortieRepository afin de recupérer les sorties filtrées
        $sortiesQuery = $sortieRepo->rechercheDetaillee(
            ($request->query->get('recherche_terme') != null ? $request->query->get('recherche_terme') : null),
            ($request->query->get('recherche_site') != null ? $request->query->get('recherche_site') : null),
            ($request->query->get('recherche_etat') != null ? $request->query->get('recherche_etat') : null),
            ($request->query->get('date_debut') != null ? $request->query->get('date_debut') : null),
            ($request->query->get('date_fin') != null ? $request->query->get('date_fin') : null),
            ($request->query->get('cb_organisateur') != null ? $request->query->get('cb_organisateur') : null),
            ($request->query->get('cb_inscrit') != null ? $request->query->get('cb_inscrit') : null),
            ($request->query->get('cb_non_inscrit') != null ? $request->query->get('cb_non_inscrit') : null),
            ($request->query->get('cb_passee') != null ? $request->query->get('cb_passee') : null)
        );
        //limitation à 10 sorties par page
        //$sorties = $paginator->paginate(
        //    $sortiesQuery,
        //    $request->query->getInt('page', 1),
        //    5
        //);
        $sorties=$sortiesQuery->getResult();
        //recuperation de tous les sites
        $sites = $siteRepo->findAll();
        //recuperation de tous les etats
        $etats = $etatRepo->findAll();

        //délégation du travail au twig liste.html.twig en y passant en parametre les sorties filtrées, les sites et les etats
        return $this->render("sortie/liste.html.twig", [
            'sorties' => $sorties,
            'sites' => $sites,
            'etats' => $etats
        ]);
    }

    /**
     * Fonction permettant d'afficher le détail d'une sortie
     * la requete SQL est :
     *      SELECT so.nom, date_heure_debut, date_limite_inscription, nb_inscriptions_max, duree,
     *          infos_sortie, vi.nom, li.nom, li.rue, vi.code_postal, li.latitude, li.longitude
     *      FROM sortie AS so
     *      INNER JOIN lieu AS li ON so.lieu_id = li.id
     *      INNER JOIN ville AS vi ON li.ville_id = vi.id
     *      LEFT JOIN participant_sortie AS ps ON so.id=ps.sortie_id
     *      WHERE so.id = ?;
     * @Route("/detail/{id}", name="detail", requirements={"id"="\d+"}, methods={"GET|POST"})
     */
    public function detail($id,SortieRepository $sortieRepo){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');

        //appel de la methode find dans SortieRepository
        $sortie = $sortieRepo->find($id);
        //si la sortie n'existe pas, levée d'une exception de type NotFoundException
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }

        //délégation du travail au twig detail.html.twig en y passant en parametre la sortie
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie
        ]);
    }

    /**
     * Fonction permettant d'ajouter une sortie
     * RDG UC 2002 : En tant qu'organisateur d'une sortie, je peux créer une nouvelle sortie ( définir un nom pour la
     * sortie, une date et heure, une durée, un lieu (nom, adresse, gps), un nombre limite de participants, une note
     * textuelle, et une date limite d'inscription )
     * la requete SQL est :
     *
     * @Route("/ajouter", name="ajouter")
     */
    public function ajouter(EtatRepository $etatRepo, EntityManagerInterface $entityManager, Request $request){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');
        //instanciation d'une sortie
        $sortie = new Sortie();
        //j'hydrate l'organisateur en récuperant l'utilisateur connecté
        $sortie->setOrganisateur($this->getUser());
        //j'hydrate le site en recuperant le site de l'utilisateur connecté
        $sortie->setSite($this->getUser()->getSite());

        //creation d'une instance de SortieType
        $sortieForm = $this ->createForm(SortieType::class, $sortie);
        //je demande à Symfony d'hydrater mon $sortie avec les données
        //recues de la requete
        $sortieForm->handleRequest($request);

        //si le formulaire est soumis et validé alors ...
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            //j'hydrate l'etat à créé ou à publiée
            if ($_POST["valider"]=="save") {
                $sortie->setEtat($etatRepo->find(1));
            } else {
                $sortie->setEtat($etatRepo->find(2));
            }
            //j'informe le serveur de l'ajout de la sortie
            $entityManager->persist($sortie);
            //je valide l'ajout
            $entityManager->flush();
            //affichage d'un message flash pour informer du bon déroulement de l'operation
            $this->addFlash("success", "La sortie a bien été créée");

            //délegation au controlleur SortieController fonction liste
            return $this->redirectToRoute('sortie_liste');
        }
        //delegation au twig ajout.html.twig en passant en parametre le formulaire (sortieForm)
        return $this->render("sortie/ajout.html.twig", [
            "form" => $sortieForm->createView()
        ]);
    }

    /**
     * Fonction permettant de s'inscrire à une sortie
     * RDG UC 2003: En tant que participant, je peux m’inscrire à une sortie. Il faut que la sortie ai été publiée
     * (ouverte), et que la date limite d’inscription ne soit pas dépassée. Et que le nombre de participant max ne soit
     * pas atteint
     * @Route("/inscrire", name="inscrire")
     */
    public function inscrire(EntityManagerInterface $entityManager, Request $request, SortieRepository $sortieRepo){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');
        //recuperation de l'id de sortie dans le contexte de requete
        $sortieid = $request->query->get('sortieid');
        //recuperation de l'utilisateur en session
        $user = $this->getUser();
        //recuperation de la sortie en BDD
        $sortie = $sortieRepo->find($sortieid);
        //si le nombre de participant est strictement inferieur au nombre maximum de participants defini ...
        if($sortie->getParticipants()->count()<$sortie->getNbInscriptionsMax()){
            //si l'état de la sortie est à 2 (ouverte) ...
            if ($sortie->getEtat()->getId() == 2){
                //si l'utilisateur connecté est déjà inscrit ...
                if ($sortie->getParticipants()->contains($user)){
                    $this->addFlash('danger', 'Vous avez déjà été inscrit à cette sortie');
                    return $this->redirectToRoute("sortie_liste");
                    //sinon
                }else{
                    //faire ceci pour alimenter la table d'intersection entre Participant et Sortie //
                    $sortie->addParticipant($user); //ajout du participant à la sortie
                    $user->addSortie($sortie);//ajout de la sortie au participant
                    $entityManager->persist($user);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', $this->getUser()->getUsername().' ,vous avez bien été inscrit à cette sortie oganisée par ' . $sortie->getOrganisateur()->getUsername() );
                    return $this->redirectToRoute("sortie_liste");
                }
                //si état de la sortie different de 2
            }else{
                $this->addFlash('danger', 'Désolé il n\'est pas ou plus possible de s\'inscrire à cette sortie');
                return $this->redirectToRoute("sortie_liste");
            }
            //si nombre maximum de participants atteint
        }else{
            $this->addFlash('danger', "Désolé, la sortie n'a plus de places disponibles.");
            return $this->redirectToRoute("sortie_liste");
        }
    }

    /**
     * Fonction permettant de se desister à une sortie
     * RDG UC 2003: En tant que participant, je peux m’inscrire à une sortie. Il faut que la sortie ai été publiée
     * (ouverte), et que la date limite d’inscription ne soit pas dépassée. Et que le nombre de participant max ne soit
     * pas atteint
     * @Route("/desister", name="desister")
     */
    public function desister(EntityManagerInterface $entityManager, Request $request, SortieRepository $sortieRepo) {
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_USER, il verra la page 403 (accès refusé)
        //$this->denyAccessUnlessGranted('ROLE_USER');

        $sortieid = $request->query->get('sortieid');
        $participant = $this->getUser();
        $sortie = $sortieRepo->find($sortieid);

        //faire ceci pour alimenter la table d'intersection entre Participant et Sortie //
        $participant->removeSortie($sortie);
        $sortie->removeParticipant($participant);

        $entityManager->persist($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash("success", "Vous n'êtes plus inscrit pour cette sortie");
        return $this->redirectToRoute("sortie_liste");
    }

    /**
     * Fonction permettant de supprimer une sortie
     *
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimer(EntityManagerInterface $entityManager, Request $request){
        //$this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $entityManager->getRepository(Sortie::class)->find($sortieid);
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        if ($sortie->getOrganisateur()->getId() == $this->getUser()->getId()){
            $sortie->getOrganisateur()->removeSortieOrganisee($sortie);
            foreach ($sortie->getParticipants() as $participant){
                $participant->removeSortie($sortie);
                $sortie->removeParticipant($participant);
                $entityManager->persist($participant);
            }
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash("success", "La sortie a bien été supprimée");
        }
        return $this->redirectToRoute("sortie_liste");
    }

    /**
     * @Route("/modifier", name="modifier")
     */
    public function modifier(EntityManagerInterface $em, Request $request){
       // $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        $sortie->setIsPublished(false);
        $sortie->setMotifAnnulation('aucun');
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        $form = $this ->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $sortie->setDateHeureDebut(new \DateTime());
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "La sortie a bien été modifiée");
            return $this->redirectToRoute("sortie_detail", ['id'=>$sortie->getId()]);
        }
        return $this->render("sortie/modification.html.twig", [
            "sortie" => $sortie,
            "form" => $form->createView()
        ]);
    }
    /**
     * @Route("/publier", name="publier")
     */
    public function publier(EntityManagerInterface $em, Request $request){
       // $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        $etat = $em->getRepository(Etat::class)->find(2);
        $sortie->setEtat($etat);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "La sortie a bien été publiée");
        return $this->redirectToRoute("sortie_liste");
    }
    /**
     * @Route("/annuler", name="annuler")
     */
    public function annuler(EntityManagerInterface $em, Request $request){
        //$this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        $form = $this->createForm(AnnulationType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()){
            if ($sortie->getOrganisateur()->getId() == $this->getUser()->getId()){
                $etat = $em->getRepository(Etat::class)->find(5);
                $sortie->setEtat($etat);
                $em->persist($sortie);
                $em->flush();
                $this->addFlash("success", "La sortie a bien été annulée");
                return $this->redirectToRoute("sortie_liste");
            }else{
                $this->addFlash("danger", "Vous ne disposez pas des droits afin d'annuler cette sortie");
                return $this->redirectToRoute("sortie_annuler");
            }
        }
        return $this->render("sortie/annulation.html.twig", [
            "sortie" => $sortie,
            "form" => $form->createView()
        ]);
    }

}
