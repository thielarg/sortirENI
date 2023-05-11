<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
     * Fonction permettant de traiter le formulaire de filtre du twig liste.html.twig
     * RDG UC 2001: En tant que participant, je peux lister les sorties publiées sur chaque site, celles auxquelles
     * je suis inscrit et celles dont je suis l’organisateur. Je peux filtrer cette liste suivant différents critères
     * (voir maquette écran)
     * la request SQL est :
     *      SELECT so.nom, date_heure_debut, date_limite_inscription, count(ps.participant_id), ps.participant_id,
     *      nb_inscriptions_max, si.nom, libelle, username
     *      FROM sortie AS so
     *      INNER JOIN site AS si ON so.site_id = si.id
     *      INNER JOIN etat AS et ON so.etat_id = et.id
     *      INNER JOIN participant AS pa ON so.organisateur_id=pa.id
     *      INNER JOIN participant_sortie AS ps ON so.id=ps.sortie_id
     *      GROUP BY so.nom, date_heure_debut, date_limite_inscription, nb_inscriptions_max, si.nom, libelle, username
     *      WHERE so.nom LIKE ? AND si.id = ? AND et.id = ? AND date_heure_debut > ?
     *           AND DATE_ADD(date_heure_debut, INTERVAL duree, MINUTE) <= ? AND so.organisateur_id = ?
     *           AND ps.participant_id = ? AND ps.participant_id != ? AND et.id = 4
     * @param null $recherche_terme Recherche les sorties par mot-clé
     * @param null $siteId Recherche les sorties par l'identifiant du site
     * @param null $etat Recherche les sorties par l'identifiant de l'état
     * @param null $date_debut Recherche les sorties dont la date de debut est supérieure à une date selectionnée
     * @param null $date_fin Recherche les sorties dont la date de fin est inférieurs à une date selectionnée
     * @param null $organisateur Recherche les sorties dont je suis l'organisateur.trice
     * @param null $inscrit Recherche les sorties auxquelles je suis inscrit.e
     * @param null $non_inscrit Recherche les sorties auxquelles je ne suis pas inscrit.e
     * @param null $passee Recherche les sorties passées
     * @return \Doctrine\ORM\Query
     * @throws \Exception
     */
    public function rechercheDetaillee($recherche_terme = null, $siteId = null,$etat = null, $date_debut = null, $date_fin = null, $organisateur = null, $inscrit = null, $non_inscrit = null, $passee = null) {
        //construction de la requete jointée naturelle avec les entités sortie/site/organisateur/etat
        $qb = $this->createQueryBuilder('sortie')
            ->join('sortie.site', 'site')
            ->join('sortie.organisateur', 'organisateur')
            ->join('sortie.etat' , 'etat')
            ->addSelect('site')
            ->addSelect('organisateur')
            ->addSelect('etat');

        //si le champs de rechercher sortie par mot clef est renseigné
        if($recherche_terme != null){
            //ajoute de la clause where à la requete paramétrée
            $qb->andWhere($qb->expr()->like('sortie.nom',':recherche_terme'))
                ->setParameter('recherche_terme', '%'.$recherche_terme.'%');
        }
        if($siteId > 0){
            $qb->andWhere('site.id = :siteId')
                ->setParameter('siteId', $siteId);
        }
        if($etat > 0){
            $qb->andWhere('etat.id = :etat')
                ->setParameter('etat', $etat);
        }
        if($date_debut != null){
            $qb->andWhere('sortie.dateHeureDebut > :date_debut')
                ->setParameter('date_debut', new \DateTime($date_debut));
        }
        if($date_fin != null){
            $qb->andWhere('sortie.dateHeureDebut < :date_fin')
                ->setParameter('date_fin', new \DateTime($date_fin));
        }
        if($organisateur != null){
            $organisateur = $user = $this->getEntityManager()->getRepository(Participant::class)->find($organisateur);
            $qb->andWhere('sortie.organisateur = :organisateur')
                ->setParameter('organisateur', $organisateur);
        }
        if($inscrit != null && $non_inscrit==null){
            $user = $this->getEntityManager()->getRepository(Participant::class)->find($inscrit);
            $qb->andWhere(':inscrit MEMBER OF sortie.participants')
                ->setParameter('inscrit', $user);
        }
        if($non_inscrit != null && $inscrit==null){
            $user = $this->getEntityManager()->getRepository(Participant::class)->find($non_inscrit);
            $qb->andWhere(':inscrit NOT MEMBER OF sortie.participants')
                ->setParameter('inscrit', $user);
        }
        if($passee != null){
            $qb->andWhere('etat.libelle = :etat')
                ->setParameter('etat', 'Passée');
        }

        return $qb->getQuery();
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
