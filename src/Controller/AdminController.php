<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ParticipantType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * Fonction permettant d'afficher la liste des participants (partie administrateur)
     * la requete SQL est :
     *      SELECT * FROM participant WHERE email = ? OR nom = ? OR prenom = ?
     * @Route("/participants", name="liste_des_participants")
     */
    public function listerParticipants(Request $request, ParticipantRepository $participantRepository){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_ADMIN, il verra la page 403 (accès refusé)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantsQuery = $participantRepository->rechercheDetaillee(
            $request->query->get('recherche_terme') != null ? $request->query->get('recherche_terme') : null);
        $participants =$participantsQuery->getResult();

        return $this->render("admin/listeParticipant.html.twig",[
            "participants" => $participants
        ]);
    }

    /**
     * Fonction permettant d'inscrire un participant à une sortie (partie administrateur)
     * RDG UC 1007: En tant qu'administrateur, je peux créer un nouvel utilisateur par un écran d'administration avec
     * saisie manuelle des informations
     * la requete SQL est :
     *      INSERT INTO participant
     *      (site_id, nom, prenom, telephone, email, administrateur, actif, username, password, photo)
     *      VALUES
     *      (?,?,?,?,?,?,?,?,?,?,?)
     * @Route("/inscrire", name="inscrire")
     */
    public function inscrire(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_ADMIN, il verra la page 403 (accès refusé)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // ou
        //$isAdmin = $this->isGranted("ROLE_ADMIN");
        //if (!$isAdmin){
        //    throw new AccessDeniedException("Réservé aux admins !");
        //}
        //creation d'une instance de Participant
        $participant = new Participant();
        //j'hydrate certains attributs
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        //$participant->setRoles(["ROLE_USER"]);
        //creation d'une instance de ParticipantType
        $form = $this->createForm(ParticipantType::class, $participant);
        //je demande à Symfony d'hydrater mon $participant avec les données
        //recues de la requete
        $form->handleRequest($request);
        //si le formulaire est soumis et valide alors :
        //- j'encode le password
        //- j'hydrate les proprietes manquantes (non present dans le formulaire)
        //- sauvegarde de l'entite en BDD
        //- ajoute un message en session pour afficher sur la prochaine page (un message flash)
        //-je redirige vers la page liste des participants

        if ($form->isSubmitted()){
            $hash=$encoder->encodePassword($participant, 'password');
            $participant->setPassword($hash);
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'Un nouveau participant a été créé');
            return $this->redirectToRoute('admin_liste_des_participants');
        }
        return $this->render('admin/inscrire.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Fonction permettant d'afficher le detail des informations d'un participant (partie administrateur),
     * afin de pouvoir activer/desactiver ou supprimer un participant
     * la requete SQL est :
     *      SELECT * FROM participant WHERE id = ?;
     * @Route("/{id}", name="participant_detail", requirements={"id"="\d+"}, methods={"GET|POST"})
     */
    public function detailParticipant($id, ParticipantRepository $participantRepo){
        //Si l'utilisateur n'est pas encore connecté, il lui sera demandé de se connecter (par exemple redirigé vers
        // la page de connexion).
        //Si l'utilisateur est connecté, mais n'a pas le rôle ROLE_ADMIN, il verra la page 403 (accès refusé)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //recherche du participant par l'id en BDD
        $participant = $participantRepo->find($id);
        if ($participant == null){
            throw $this->createNotFoundException('Ce participant n\'existe pas !');
        }
        if ($participant->getSite()==null){
            $site = new Site();
            $site->setNom("<<non renseigné");
            $participant->setSite($site);
        }
        //-je redirige vers la page détail du participant
        return $this->render('admin/detailParticipant.html.twig', [
            'participant' => $participant
        ]);
    }

    /*
     * Fonction permettant de modifier les informations d'un utilisateur dans la BDD (partie administrateur)
     * RDG UC
     * la requete SQL est :
     * @Route("/modifier/{id}", name="modifier", requirements={"id"="\d+"})
     */
    public function modifierParticipant($id, EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepo){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //$participantid = $request->query->get('participantid');
        $participant = $participantRepo->find($id);
        //creation d'une instance de ParticipantType
        $form = $this->createForm(ParticipantType::class, $participant);
        //je demande à Symfony d'hydrater mon $participant avec les données
        //recues de la requete
        $form->handleRequest($request);
        //si le formulaire est soumis et valide alors :
        //- sauvegarde de l'entite en BDD
        //- ajoute un message en session pour afficher sur la prochaine page (un message flash)
        //-je redirige vers la page liste des participants
        if ($form->isSubmitted()) {
            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Le participant a été modifié');
            return $this->redirectToRoute('admin_liste_des_participants');
        }
        return $this->render('admin/modifier.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Fonction permettant d'activer un utilisateur dans la BDD (partie administrateur)
     * RDG UC 1008: En tant qu'administrateur, je peux rendre actif des utilisateurs sélectionnés dans une liste
     * d'utilisateurs.
     * la requete SQL est :
     *      UPDATE participant SET actif = 1 WHERE id = ?;
     * @Route("/activer", name="activer")
     */
    public function activer(EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepo){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantid = $request->query->get('participantid');
        $participant = $participantRepo->find($participantid);
        $participant->setActif(true);
        $em->persist($participant);
        $em->flush();
        $this->addFlash('success', 'Le participant a été activé');
        return $this->redirectToRoute('admin_liste_des_participants');
    }

    /**
     * Fonction permettant de désactiver un utilisateur dans la BDD (partie administrateur)
     * RDG UC 1008: En tant qu'administrateur, je peux rendre inactif des utilisateurs sélectionnés dans une liste
     * d'utilisateurs.
     * la requete SQL est :
     *      UPDATE participant SET actif = 0 WHERE id = ?;
     * @Route("/desactiver", name="desactiver")
     */
    public function desactiver(EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepo){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantid = $request->query->get('participantid');
        $participant = $participantRepo->find($participantid);
        $participant->setActif(false);
        $em->persist($participant);
        $em->flush();
        $this->addFlash('success', 'Le participant a été désactivé');
        return $this->redirectToRoute('admin_liste_des_participants');
    }

    /**
     * Fonction permettant de supprimer un utilisateur de la BDD (partie admoinistrateur)
     * RGD UC 1009: En tant qu'administrateur, je peux supprimer des utilisateurs sélectionné dans une liste
     * d'utilisateurs
     * les requetes SQL sont, dans l'ordre :
     *      DELETE FROM sortie WHERE organisateur_id = ?;
     *      DELETE FROM participant_sortie WHERE participant_id = ?;
     *      DELETE FROM participant WHERE id = ?;
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimer(EntityManagerInterface $em, Request $request, ParticipantRepository $participantRepo){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //recuperation de l'identifiant du participant à supprimer contenu dans le contexte de requete
        $participantid = $request->query->get('participantid');
        //chargement de l'instance participant
        $participant = $participantRepo->find($participantid);
        if ($participant == null){
            throw $this->createNotFoundException('Ce participant n\'existe pas !');
        }
        //pour chaque sortie où l'utilisateur est participant ou organisateur
        //1ere phase suppression des participants inscrits sur les sorties de cet organisateur

        //2eme phase suppression des sorties de cet organisateur
        //3eme phase suppression des lignes de sortie auxquelles je participe
        foreach ($participant->getSorties() as $sorties){
            $participant->removeSortie($sorties); //suppression des sorties organisées par ce participant
            $sorties->removeParticipant($participant); //suppression du participant des sorties auxquelles il participe
            $em->persist($sorties);
        }
        //4eme phase suppression du participant
        $em->remove($participant);//suppression du participant
        $em->flush();
        $this->addFlash('success', 'Le participant a bien été supprimé');
        return $this->redirectToRoute("admin_liste_des_participants");
    }

    /**
     * Fonction permettant d'annuler une sortie (partie administrateur)
     * RDG UC 2012: En tant qu'administrateur, je peux annuler une sortie qui a été proposée par un autre participant.
     * la requete SQL est :
     *      UPDATE sortie SET etat = 5 WHERE id = ?;
     * les états sont :
     *      Créée = 1
     *      Publiée = 2
     *      En-cours = 3
     *      Terminée = 4
     *      Annulée = 5
     * @Route("/sortie/annuler", name="annuler_sortie")
     */
    public function annulerSortie(EntityManagerInterface $em, Request $request, SortieRepository $sortieRepo, EtatRepository $etatRepo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //recuperation de l'identifiant de la sortie dans le contexte de requete
        $sortieid = $request->query->get('sortieid');
        //recherche de la sortie en BDD
        $sortie = $sortieRepo->find($sortieid);
        //si l'etat de la sortie est créée, publiée ou en-cours ...
        if (($sortie->getEtat()->getId() == 1) || ($sortie->getEtat()->getId() == 2) || ($sortie->getEtat()->getId() == 3)) {
            if ($sortie == null) {
                throw $this->createNotFoundException('Cette sortie n\'existe pas !');
            }
            //recherche de l'etat 5: Annulée
            $etat = $etatRepo->find(5);
            if ($etat == null) {
                throw $this->createNotFoundException('Cet état n\'existe pas !');
            }
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a bien été annulée');
            return $this->redirectToRoute("sortie_liste");
        }
        //etat de la sortie 4: Terminée ou 5: Annulée
        $this->addFlash('danger', 'Le statut de la sortie ne permet pas de l\'annuler');
        return $this->redirectToRoute("sortie_liste");
    }

}
