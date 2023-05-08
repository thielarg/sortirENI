<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/participant", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * Fonction permettant de modifier les informations du profil de la personne connectée
     * RDG UC 1003 - En tant qu’utilisateur, je peux gérer mes informations de profil, notamment mon nom, prénom, pseudo,
     * email, mot de passe, et téléphone. Le pseudo doit être unique entre tous les participants.
     * la requete SQL est : UPDATE participants SET ... WHERE id= ?
     * @Route("/modifier", name="modifier")
     */
    public function modifier(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder){
        //récupération de l'utilisateur connecté
        $participant = $this->getUser();
        //creation d'une instance de ParticipantType
        $modifForm = $this->createForm(ParticipantType::class, $participant);
        //je demande à Symfony d'hydrater mon $participant avec les données
        //recues de la requete
        $modifForm->handleRequest($request);

        //si le formulaire est soumis et valide alors :
        //- chargement de la photo, puis encodage MD5 du nom de la photo et stockage sur le serveur. Puis j'hydrate la
        // photo dans l'objet
        //- Hashage du password puis j'hydrate le mot de passe hashé dans l'objet
        //- sauvegarde de l'entite en BDD
        //- ajoute un message en session pour afficher sur la prochaine page (un message flash)
        //- je redirige vers la même page
        if ($modifForm->isSubmitted() && $modifForm->isValid()){
            if($modifForm->get('photo')->getData() != null){
                //chargement de la photo
                $file = $modifForm->get('photo')->getData();
                //encodage MD5 du nom de la photo
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                //stockage sur le serveur
                $file->move($this->getParameter('users_photos_directory'), $fileName);
                //hydrate la photo dans l'objet
                $participant->setPhoto($fileName);
            }
            //hashage du password
            $hash=$encoder->encodePassword($participant, $participant->getPassword());
            //hydrate le mot de passe hashé dans l'objet
            $participant->setPassword($hash);
            //sauvegarde de l'entite en BDD
            $em->persist($participant);
            $em->flush();
            //ajoute un message en session pour afficher sur la prochaine page (un message flash)
            $this->addFlash('success', 'Votre profil a été modifié');
            //redirige vers la même page
            $this->redirectToRoute("participant_modifier");
        }

        return $this->render('participant/add.html.twig', [
            'form' => $modifForm->createView()
        ]);
    }

    /**
     * Fonction permettant d'afficher les informations d'un participant
     * RDG UC 1003 - En tant qu’utilisateur, je peux gérer mes informations de profil, notamment mon nom, prénom, pseudo,
     * email, mot de passe, et téléphone. Le pseudo doit être unique entre tous les participants.
     * la requete SQL est : SELECT ... FROM participants WHERE id= ?
     * @Route("/detail/{id}", name="detail",
     *     requirements={"id"="\d+"}, methods={"GET|POST"})
     */
    public function details(EntityManagerInterface $entityManager, $id) {
        $participant = $entityManager->getRepository(Participant::class)->find($id);
        return $this->render( 'participant/detail.html.twig', [
            'participant' => $participant
        ]);
    }

}
