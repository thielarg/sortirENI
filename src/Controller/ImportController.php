<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;


class ImportController extends AbstractController
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }


    /**
     * @Route("/admin/import_index", name="admin_import_index")
     */
    public function import_index() {
        return $this->render('/admin/importCSV.html.twig');
    }

    /**
     * @Route("/admin/import_action", name="admin_import_action")
     */
    public function import_action(
        EntityManagerInterface $em,
        Request $request,
        SiteRepository $siteRepo,
        ParticipantRepository $participantRepo)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //si nom du fichier non renseigné dans le champ de saisie
        if ($request->files->get('fileCSV') != null) {
            //si l'extension du fichier est bien au format csv
            if(($request->files->get('fileCSV'))->getClientOriginalExtension() == 'csv') {
                //recuperation du fichier
                $fileCSV = $request->files->get('fileCSV');
                //déplacement du fichier dans le repertoire admin declaré dans le fichier services.yaml
                $fileCSV->move($this->getParameter('admin_csv_directory'), 'utilisateurs.csv');
                $utilisateurs = array(); // Tableau qui va contenir les éléments extraits du fichier CSV
                $row = 0; // Représente la ligne

                // Import du fichier CSV
                //si le fichier est ouvert en lecture ...
                if (($handle = fopen($this->getParameter('admin_csv_directory') . '/utilisateurs.csv', "r")) !== FALSE) { // Lecture du fichier, à adapter
                    //tant qu'il y a des lignes à lire, avec un maximum à 1000 ... je charge chaque element de la ligne dans un tableau
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Eléments séparés par une virgule, à modifier si nécessaire
                        $num = count($data); // Nombre d'éléments sur la ligne traitée
                        $row++;

                        $data = str_replace('&quot;',"",htmlentities( $data[0],ENT_QUOTES));
                        $data = explode(",",$data);

                        //pour chaque ligne recuperée, je charge le tableau contenant un tableau associatif
                        $utilisateurs[$row] = array(
                            "siteid" => $data[0],
                            "nom" => $data[1],
                            "prenom" => $data[2],
                            "telephone" => $data[3],
                            "email" => $data[4],
                            "pseudo"=>$data[5],
                            "administrateur" => $data[6],
                            "actif" => $data[7],
                            "password" => $data[8],
                        );
                    }
                    //fermeture du fichier
                    fclose($handle);
                }

                // Lecture du tableau contenant les utilisateurs et ajout dans la base de données
                foreach ($utilisateurs as $utilisateur) {

                    // On crée un objet utilisateur
                    $user = new Participant();

                    // Encodage du mot de passe
                    $encoder = $this->encoderFactory->getEncoder($user);
                    $plainpassword = $utilisateur["password"];
                    $password = $encoder->encodePassword($plainpassword, $user->getSalt());

                    // Récupération du site dans la base de données
                    $site = $siteRepo->find($utilisateur["siteid"]);

                    // Hydrate l'objet avec les informations provenants du fichier CSV
                    $user->setSite($site);
                    $user->setNom(str_replace('\"', '', $utilisateur["nom"]));
                    $user->setPrenom(str_replace('\"', '', $utilisateur["prenom"]));
                    $user->setTelephone(str_replace('\"', '', $utilisateur["telephone"]));
                    $user->setEmail(str_replace('\"', '', $utilisateur["email"]));
                    $user->setPseudo(str_replace('\"', '', $utilisateur["pseudo"]));
                    $user->setAdministrateur($utilisateur["administrateur"]);
                    $user->setActif($utilisateur["actif"]);
                    $user->setPassword($password);

                    //test l'enregistrement avant d'insérer
                    $userTestExistence = $participantRepo->findOneByEmail($user->getEmail());
                    if ($userTestExistence == null) {
                        // Enregistrement de l'objet en vu de son écriture dans la base de données
                        $em->persist($user);
                    }
                }

                // Ecriture dans la base de données
                $em->flush();

                // Renvoi la réponse
                $this->addFlash('success', 'Import réussi !');

                // Redirection
                return $this->redirectToRoute('admin_liste_des_participants');
            }
            //si mauvaise extension de fichier, alors ...
            $this->addFlash('danger', 'Merci de charger un fichier au format CSV');
        }
        //pas de nom de fichier saisi, alors ...
        $this->addFlash('danger', 'Aucun fichier choisi');
        return $this->redirectToRoute('admin_import_index');
    }
}
